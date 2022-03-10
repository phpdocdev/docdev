package main

import (
	"fmt"
	"log"
	"os"
	"os/exec"

	"github.com/joho/godotenv"
	"github.com/urfave/cli"
)

func Init (c *cli.Context) error {
	_, err := exec.Command("cp", ".env.example", ".env").Output()
    if err != nil {
        fmt.Printf("%s", err)
    }

	mkcert, err := exec.Command("which", "mkcert").Output()

	if string(mkcert[:]) == "" {
		_, err = exec.Command("brew", "install", "mkcert").Output()
		if err != nil {
			fmt.Printf("%s", err)
		}
	}

	return err
}

func GenerateCerts(c *cli.Context) error {

	nameCmd := `ls ` + os.Getenv("DOCUMENTROOT") + ` | grep -v / | tr '\n' " " | sed 's/ /\.\l\o\c /g'`
	names, err := exec.Command("bash", "-c",  nameCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	mkCertCmd := "mkcert -cert-file cert/nginx.pem -key-file cert/nginx.key localhost 127.0.0.1 ::1 "+string(names[:])
	_, err = exec.Command("bash", "-c",  mkCertCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	mkCertPath, err := exec.Command("mkcert", "-CAROOT").Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	mkCertPath = mkCertPath[:len(mkCertPath)-1]

	cpCertCmd := `cp -R "` + string(mkCertPath[:]) + `"/ ./cert/`
	fmt.Println(cpCertCmd)
	_, err = exec.Command("bash", "-c",  cpCertCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	return err
}

func StartContainer(c *cli.Context) error {
	startCmd := `docker-compose up --quiet-pull --force-recreate --build -V -d`
	start := exec.Command("bash", "-c",  startCmd)

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
	_, err = exec.Command("bash", "-c",  copyCertsCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	refreshCertsCmd := `docker exec php` + os.Getenv("PHPV") + ` sudo update-ca-certificates --fresh`
	_, err = exec.Command("bash", "-c",  refreshCertsCmd).Output()
	if err != nil {
		fmt.Printf("%s", err)
	}

	return err
}

func ExecContainer(c *cli.Context) error {
	execCmd := `docker exec -ti php` + os.Getenv("PHPV") + ` /bin/zsh`
	cmd := exec.Command("bash", "-c",  execCmd)

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
	myEnv, err := godotenv.Read()
	if err != nil {
        return err
    }

	myEnv["PHPV"] = c.Args().First()
	err = godotenv.Write(myEnv, "./.env")
	if err != nil {
        return err
    }

	return err
}

func loadEnv() {
	err := godotenv.Load()
	if err != nil {
		log.Fatal("Error loading .env file")
	}
}

func main() {
	if _, err := os.Stat(".env"); os.IsNotExist(err) {
		os.Chdir("../")
	}
	loadEnv()

	app := &cli.App{
		Commands: []cli.Command{
		  {
			Name:    "init",
			Aliases: []string{"a"},
			Usage:   "Initialize configuration and install mkcert",
			Action:  Init,
		  },
		  {
			Name:    "certs",
			Aliases: []string{"c"},
			Usage:   "Generate and install the certificates",
			Action:  GenerateCerts,
		  },
		  {
			Name:    "start",
			Aliases: []string{"s"},
			Usage:   "Bring up the docker containers",
			Action:  StartContainer,
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
			Usage:   "Start docker container shell",
			Action:  ChangePhpVersion,
		  },
		},
	  }
	
	  err := app.Run(os.Args)
	  if err != nil {
		log.Fatal(err)
	  }
}

// func execute() {

// 	var name string
// 	var schedule string
// 	var command string

// 	flag.StringVar(&name, "name", "", "Job Name")
// 	flag.StringVar(&name, "n", "", "Job Name (shorthand)")

// 	flag.Parse()

//     // here we perform the pwd command.
//     // we can store the output of this in our out variable
//     // and catch any errors in err
//     out, err := exec.Command("ls").Output()

//     // if there is an error with our execution
//     // handle it here
//     if err != nil {
//         fmt.Printf("%s", err)
//     }
//     // as the out variable defined above is of type []byte we need to convert
//     // this to a string or else we will see garbage printed out in our console
//     // this is how we convert it to a string
//     fmt.Println("Command Successfully Executed")
//     output := string(out[:])
//     fmt.Println(output)

//     // let's try the pwd command herer
//     out, err = exec.Command("pwd").Output()
//     if err != nil {
//         fmt.Printf("%s", err)
//     }
//     fmt.Println("Command Successfully Executed")
//     output = string(out[:])
//     fmt.Println(output)
// }