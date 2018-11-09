package main

import (
	"bufio"
	"bytes"
	"crypto/sha256"
	"encoding/json"
	"errors"
	"flag"
	"fmt"
	"io"
	"io/ioutil"
	"os"
	"runtime/debug"
	"strings"
	"sync"

	"github.com/kr/pretty"
)

var fromPath = "./public"
var targetPath = "./newpublic"
var verbose bool

var mainPackagesJson = "packages.json"

type PackagePath struct {
	SHA256 string `json:"sha256"`
}

type Packages struct {
	Packages         []interface{}           `json:"packages"`
	Notify           string                  `json:"notify"`
	NotifyBatch      string                  `json:"notify-batch"`
	ProvidersURL     string                  `json:"providers-url"`
	Search           string                  `json:"search"`
	ProviderIncludes map[string]*PackagePath `json:"provider-includes"`
}

type Provider struct {
	Providers map[string]*PackagePath `json:"providers"`
}

var packages = map[string]map[string]*PackagePath{}

var hashed = map[string]string{}
var hashedLock sync.RWMutex

var replaceMap = map[string]string{
	"https:\\/\\/api.github.com": "https:\\/\\/github-api-proxy.cnpkg.org",
	"https://api.github.com":     "https://github-api-proxy.cnpkg.org",
}

func main() {
	debug.SetMaxThreads(100000)

	flag.StringVar(&fromPath, "from", fromPath, "From path")
	flag.StringVar(&targetPath, "target", targetPath, "Target path")
	flag.BoolVar(&verbose, "verbose", verbose, "Show verbose outputs")
	flag.Parse()

	if err := buildHashed("hashed.log"); err != nil {
		logErr("%s", err.Error())
		return
	}

	mainPackages := getMainPackages(mainPackagesJson)
	for name, p := range mainPackages.ProviderIncludes {
		// packages := getPackages(normalize(name, p))
		packages[name] = make(map[string]*PackagePath)
		for packName, pPath := range getPackages(normalizeProvider(name, p.SHA256)) {
			packages[name][packName] = pPath
		}
	}

	if verbose {
		fmt.Printf("Provider: %d\n", len(packages))
		for name, _ := range mainPackages.ProviderIncludes {
			fmt.Printf("Provider: %s Package: %d\n", name, len(packages[name]))
		}
	}

	providersFileHashMap := map[string]string{}

	type item struct {
		name string
		p    *PackagePath
		wg   *sync.WaitGroup
	}

	jobs := make(chan item, 100)
	for i := 0; i < 10; i++ {
		go func() {
			for job := range jobs {
				if v, ok := isHashed(job.name, job.p.SHA256); ok {
					job.p.SHA256 = v
					goto END
				}
				if old, new, err := rehash(job.name, job.p); err != nil {
					logErr("%s", err.Error())
				} else {
					hashedLock.Lock()
					hashed[job.name+old] = new
					hashedLock.Unlock()
					job.p.SHA256 = new // update new hash
					fmt.Printf("[OK] %s (%s)\n", job.name, new)
				}
			END:
				job.wg.Done()
			}
		}()
	}

	for ps, p1 := range packages {
		var wg sync.WaitGroup
		wg.Add(len(p1))
		for name, p2 := range p1 {
			jobs <- item{name, p2, &wg}
		}
		wg.Wait()

		newProvider := Provider{}
		newProvider.Providers = packages[ps]
		buf, err := json.Marshal(&newProvider)
		if err != nil {
			logErr("JSON Marshal Provider failed: %s", err.Error())
			continue
		}
		newHash := hmacSHA256(buf)
		newProviderFile := normalizeProvider(ps, newHash)

		if verbose {
			fmt.Println(newProviderFile)
		}

		if _, err := writeFile(newProviderFile, buf); err != nil {
			logErr("Write Provider file failed: %s", err.Error())
			continue
		}
		providersFileHashMap[ps] = newHash
	}

	if verbose {
		fmt.Printf("providersFileHashMap: %#v\n", pretty.Formatter(providersFileHashMap))
		fmt.Printf("mainPackages.ProviderIncludes: %#v\n", pretty.Formatter(mainPackages.ProviderIncludes))
	}

	if _, err := writeHashed("hashed.log"); err != nil {
		logErr("%s", err.Error())
	}

	// Wirte main packages
	for name, p := range mainPackages.ProviderIncludes {
		if v, ok := providersFileHashMap[name]; ok {
			p.SHA256 = v
		}
	}
	buf, err := json.Marshal(mainPackages)
	if err != nil {
		logErr("JSON Marshal main packages file failed: %s", err.Error())
		os.Exit(1)
	}

	if _, err := writeFile(mainPackagesJson, buf); err != nil {
		logErr("Write Provider file failed: %s", err.Error())
		os.Exit(1)
	}

}

func rehash(name string, p *PackagePath) (string, string, error) {
	packFile := normalizePackage(name, p.SHA256)
	oldHash, err := getHashByFilename(packFile)
	if err != nil {
		return "", "", fmt.Errorf("Invalid filename: %s", err.Error())
	}
	buf, err := readFile(packFile)
	if err != nil {
		return "", "", err
	}
	if hmacSHA256(buf) != oldHash {
		return "", "", fmt.Errorf("Invalid hash with: %s", packFile)
	}
	newBuf := replace(buf)
	newHash := hmacSHA256(newBuf)
	newFile := normalizePackage(name, newHash)
	if _, err := writeFile(newFile, newBuf); err != nil {
		return "", "", err
	}
	return oldHash, newHash, nil
}

func hmacSHA256(buf []byte) string {
	h := sha256.New()
	h.Write(buf)
	bs := h.Sum(nil)

	return fmt.Sprintf("%x", bs)
}

func replace(buf []byte) []byte {
	str := string(buf)
	for k, v := range replaceMap {
		str = strings.Replace(str, k, v, -1)
	}
	return []byte(str)
}

func getHashByFilename(name string) (string, error) {
	if !strings.Contains(name, "$") {
		return "", errors.New("missing '$' in " + name)
	}
	if !strings.HasSuffix(name, ".json") {
		return "", errors.New("missing '.json' in " + name)
	}
	if chunks := strings.Split(name, "$"); len(chunks) == 2 {
		return chunks[1][:len(chunks[1])-5], nil
	}
	return "", errors.New("too many '$' in " + name)
}

func buildHashed(name string) error {
	file, err := os.Open(targetPath + "/" + name)
	if err != nil {
		return err
	}
	reader := bufio.NewReader(file)
	for {
		line, _, err := reader.ReadLine()
		if err == io.EOF {
			break
		}
		if chunks := strings.Split(string(line), "="); len(chunks) == 2 {
			hashed[chunks[0]] = chunks[1]
		}
	}
	fmt.Printf("Hashed: %d\n", len(hashed))
	return nil
}

func writeHashed(name string) (int64, error) {
	file, err := os.Create(targetPath + "/" + name)
	if err != nil {
		return 0, err
	}
	var buf bytes.Buffer
	for old, new := range hashed {
		buf.WriteString(old)
		buf.WriteString("=")
		buf.WriteString(new)
		buf.WriteString("\n")
	}
	return buf.WriteTo(file)
}

func isHashed(name, hash string) (string, bool) {
	hashedLock.RLock()
	defer hashedLock.RUnlock()

	if v, ok := hashed[name+hash]; ok {
		return v, true
	}
	return "", false
}

func getMainPackages(name string) *Packages {
	buf, err := readFile(name)
	if err != nil {
		logErr("%s", err.Error())
		return nil
	}
	p := Packages{}
	if err := json.Unmarshal(buf, &p); err != nil {
		logErr("%s", err.Error())
		return nil
	}
	return &p
}

func normalizeProvider(name, hash string) string {
	return strings.Replace(name, "%hash%", hash, -1)
}

func normalizePackage(name, hash string) string {
	// /p/%package%$%hash%.json
	return "p/" + name + "$" + hash + ".json"
}

func getPackages(provider string) map[string]*PackagePath {
	buf, err := readFile(provider)
	if err != nil {
		logErr("%s", err.Error())
		return nil
	}

	p := Provider{}
	if err := json.Unmarshal(buf, &p); err != nil {
		logErr("%s", err.Error())
		return nil
	}
	return p.Providers
}

func readFile(name string) ([]byte, error) {
	file, err := os.Open(fromPath + "/" + name)
	if err != nil {
		return nil, err
	}
	defer file.Close()
	return ioutil.ReadAll(file)
}

func writeFile(name string, buf []byte) (int64, error) {
	filePath := targetPath + "/" + name
	dir := filePath[:strings.LastIndex(filePath, "/")]
	if f, err := os.Stat(dir); (err != nil) || (!f.IsDir()) {
		if err := os.MkdirAll(dir, 0777); err != nil {
			return 0, err
		}
	}

	file, err := os.Create(filePath)
	if err != nil {
		return 0, err
	}
	defer file.Close()
	return bytes.NewBuffer(buf).WriteTo(file)
}

func logErr(format string, a ...interface{}) {
	fmt.Fprintf(os.Stderr, "[ERROR] "+format+"\n", a...)
}
