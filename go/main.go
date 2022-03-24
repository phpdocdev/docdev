package main

import (
	"fmt"
	"io/ioutil"
	"log"
	"os"
	"os/exec"
	"regexp"
	"strings"

	"github.com/joho/godotenv"
	"github.com/txn2/txeh"
	"github.com/urfave/cli/v2"
)

func main() {
	if _, err := os.Stat(".env"); os.IsNotExist(err) {
		os.Chdir("../")
	}
	loadEnv()

	flags := []cli.Flag{
		&cli.BoolFlag{
			Name:    "dry-run",
			Aliases: []string{"d"},
			Usage:   "Dry run",
		},
	}

	app := &cli.App{
		Flags: flags,
		Commands: []*cli.Command{
			{
				Name:    "init",
				Aliases: []string{"i"},
				Usage:   "Initialize configuration and install mkcert",
				Action:  Init,
				Flags: append([]cli.Flag{
					&cli.StringFlag{
						Name:    "tld",
						Aliases: []string{"t"},
						Value:   "loc",
						Usage:   "TLD for project hostnames",
					},
					&cli.StringFlag{
						Name:    "root",
						Aliases: []string{"r"},
						Value:   os.Getenv("HOME") + "/repos/",
						Usage:   "Root directory containing your projects",
					},
					&cli.StringFlag{
						Name:    "php",
						Aliases: []string{"p"},
						Value:   "74",
						Usage:   "Initial PHP version",
					},
					&cli.BoolFlag{
						Name:  "certs",
						Usage: "Generate and install certificates",
					},
					&cli.BoolFlag{
						Name:  "hosts",
						Usage: "Generate hosts file",
					},
					&cli.BoolFlag{
						Name:  "start",
						Usage: "Start containers immediately",
					},
				}, flags...),
			},
			{
				Name:    "certs",
				Aliases: []string{"c"},
				Usage:   "Generate and install the certificates",
				Action:  GenerateCerts,
			},
			{
				Name:    "hosts",
				Aliases: []string{},
				Usage:   "Generate hosts file, backed up and produced at ./host. Will replace your system hostfile.",
				Action:  GenerateHosts,
				Flags:   flags,
			},
			{
				Name:    "start",
				Aliases: []string{"s"},
				Usage:   "Bring up the docker containers",
				Action:  StartContainer,
				Flags: []cli.Flag{
					&cli.BoolFlag{
						Name:    "exec",
						Aliases: []string{"e"},
						Usage:   "Start container shell after starting",
					},
				},
			},
			{
				Name:    "exec",
				Aliases: []string{"e"},
				Usage:   "Start docker container shell",
				Action:  ExecContainer,
			},
			{
				Name:    "php",
				Aliases: []string{"p"},
				Usage:   "Change php version (requires \"start\" to rebuild). Valid values: 54, 56, 72, 74",
				Action:  ChangePhpVersion,
				Flags: []cli.Flag{
					&cli.BoolFlag{
						Name:    "start",
						Aliases: []string{"s"},
						Usage:   "Start the containers after switching the PHP version",
					},
				},
			},
		},
	}

	err := app.Run(os.Args)
	if err != nil {
		log.Fatal(err)
	}
}

func Init(c *cli.Context) error {
	_, err := exec.Command("cp", ".env.example", ".env").Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	setEnvFileValue("TLD_SUFFIX", c.String("tld"))
	setEnvFileValue("DOCUMENTROOT", c.String("root"))
	setEnvFileValue("PHPV", c.String("php"))

	fmt.Printf("%s", "Created .env file\n")

	mkcert, err := exec.Command("which", "mkcert").Output()

	if string(mkcert[:]) == "" {
		_, err = exec.Command("brew", "install", "mkcert").Output()
		if err != nil {
			fmt.Printf("%s", err)
		}
	}

	if c.Bool("certs") {
		fmt.Printf("%s", "Generating certificates...\n")
		err = GenerateCerts(c)
		if err != nil {
			return cli.Exit(err, 86)
		}
	}
	if c.Bool("hosts") {
		fmt.Printf("%s", "Generating hosts...\n")
		err = GenerateHosts(c)
		if err != nil {
			return cli.Exit(err, 86)
		}
	}
	if c.Bool("start") {
		fmt.Printf("%s", "Starting containers...\n")
		err = StartContainer(c)
		if err != nil {
			return cli.Exit(err, 86)
		}
	}

	return err
}

func GenerateCerts(c *cli.Context) error {
	names := getProjectHosts()

	mkCertCmd := "mkcert -cert-file cert/nginx.pem -key-file cert/nginx.key localhost 127.0.0.1 ::1 " + names
	_, err := exec.Command("bash", "-c", mkCertCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	mkCertPath, err := exec.Command("mkcert", "-CAROOT").Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	mkCertPath = mkCertPath[:len(mkCertPath)-1]

	cpCertCmd := `cp -Rf "` + string(mkCertPath[:]) + `"/ ./cert/`
	_, err = exec.Command("bash", "-c", cpCertCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	fmt.Printf("%s", "Certifcates have been generated.\n")

	certInstalled, err := exec.Command("security", "find-certificate", "-a", "-c", "mkcert").Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	if string(certInstalled) == "" {
		fmt.Printf("Root CA is not installed.\n")
		_, err := exec.Command("sudo", "security", "add-trusted-cert", "-d", "-r", "trustRoot", "-k", "/Library/Keychains/System.keychain", "./cert/rootCA.pem").Output()
		if err != nil {
			fmt.Printf("%s", err)
		}
		fmt.Printf("Root CA has been installed.\n")
	}

	return err
}

func GenerateHosts(c *cli.Context) error {
	hostctl, err := exec.Command("which", "hostctl").Output()
	if string(hostctl[:]) == "" {
		_, err = exec.Command("brew", "install", "hostctl").Output()
		if err != nil {
			fmt.Printf("%s", err)
		}
	}

	_, err = exec.Command("hostctl", "backup", "--path", "host/").Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	hosts, err := txeh.NewHostsDefault()
	if err != nil {
		panic(err)
	}

	nameList := getProjectHosts()

	removeHosts := strings.Split(nameList, " ")
	hosts.RemoveHosts(deleteEmptySlice(removeHosts))

	addHosts := strings.ReplaceAll(nameList, ".loc", "."+os.Getenv("TLD_SUFFIX"))
	addHostList := strings.Split(addHosts, " ")
	hosts.AddHosts("127.0.0.1", deleteEmptySlice(addHostList))

	err = hosts.SaveAs("host/modified.hosts")
	if err != nil {
		fmt.Printf("%s", err)
	}

	if c.Bool("dry-run") == false {
		_, err = exec.Command("sudo", "hostctl", "restore", "--from", "host/modified.hosts").Output()
		if err != nil {
			fmt.Printf("%s", err)
		}
	}

	fmt.Printf("%s", "Host file has been generated.\n")

	return err
}

func StartContainer(c *cli.Context) error {

	fmt.Printf("Removing existing php-fpm container.\n")
	downCmd := `docker-compose rm -s -f -v php-fpm`
	exec.Command("bash", "-c", downCmd).Output()
	
	fmt.Printf("Build php-fpm container.\n")
	buildCmd := `docker-compose build php-fpm`
	exec.Command("bash", "-c", buildCmd).Output()
	
	fmt.Printf("Starting all containers.\n")
	startCmd := `docker-compose up -d`
	start := exec.Command("bash", "-c", startCmd)

	fmt.Printf("%v\n", start)
	start.Stdout = os.Stdout
	start.Stderr = os.Stderr
	start.Stdin = os.Stdin

	err := start.Start()
	if err != nil {
		return err
	}

	if err := start.Wait(); err != nil {
		return err
	}

	if err != nil {
		fmt.Printf("%s", err)
	}

	copyCertsCmd := `docker exec php` + os.Getenv("PHPV") + ` sudo cp -r /etc/ssl/cert/. /etc/ssl/certs/`
	_, err = exec.Command("bash", "-c", copyCertsCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	refreshCertsCmd := `docker exec php` + os.Getenv("PHPV") + ` sudo update-ca-certificates --fresh`
	_, err = exec.Command("bash", "-c", refreshCertsCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	if c.Bool("exec") {
		return ExecContainer(c)
	}

	return err
}

func ExecContainer(c *cli.Context) error {
	execCmd := `docker exec -ti php` + os.Getenv("PHPV") + ` zsh`
	cmd := exec.Command("bash", "-c", execCmd)

	env := os.Environ()
	cmd.Env = env

	fmt.Printf("%v\n", cmd)
	cmd.Stdout = os.Stdout
	cmd.Stderr = os.Stderr
	cmd.Stdin = os.Stdin

	err := cmd.Start()
	if err != nil {
		return err
	}

	if err := cmd.Wait(); err != nil {
		return err
	}
	return nil
}

func ChangePhpVersion(c *cli.Context) error {
	err := setEnvFileValue("PHPV", c.Args().First())

	if c.Bool("start") {
		fmt.Printf("%s", "Starting containers...\n")
		err = StartContainer(c)
		if err != nil {
			return cli.Exit(err, 86)
		}
	}

	// Update the DOCDEV_PHP env for use for other applications
	envVal := "php" + c.Args().First()
	profileLocation := os.Getenv("HOME") + "/.zshrc"
	if _, err := os.Stat(profileLocation); os.IsNotExist(err) {
		profileLocation = os.Getenv("HOME") + "/.bashrc"
	}

	dat, _ := os.ReadFile(profileLocation)
	split := strings.Split(string(dat), "\n")

	var found bool = false
	for idx, line := range split {
		if strings.HasPrefix(line, "export DOCDEV_PHP") {
			found = true
			re := regexp.MustCompile(`=.*`)
			fix := re.ReplaceAllString(line, "="+envVal)
			split[idx] = fix

		}
	}

	if !found {
		split = append(split, "export DOCDEV_PHP="+envVal)
	}

	err = ioutil.WriteFile(profileLocation, []byte(strings.Join(split, "\n")), 0)

	return err
}

func loadEnv() {
	err := godotenv.Load()
	if err != nil {
		log.Fatal("Error loading .env file")
	}
}

func deleteEmptySlice(s []string) []string {
	var r []string
	for _, str := range s {
		if str != "" {
			r = append(r, str)
		}
	}
	return r
}

func getProjectHosts() string {
	nameCmd := `ls ` + os.Getenv("DOCUMENTROOT") + ` | grep -v / | tr '\n' " " | sed 's/ /\.\l\o\c /g'`
	names, err := exec.Command("bash", "-c", nameCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	return string(names[:])
}

func setEnvFileValue(key string, value string) error {
	myEnv, err := godotenv.Read()
	if err != nil {
		return err
	}

	myEnv[key] = value
	err = godotenv.Write(myEnv, "./.env")
	if err != nil {
		log.Fatal("Error writing .env file")
	}

	err = godotenv.Overload("./.env")
	if err != nil {
		log.Fatal("Error loading .env file")
	}

	return err
}
