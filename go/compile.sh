#!/usr/bin/env bash

package=$1
if [[ -z "$package" ]]; then
  echo "usage: $0 <package-name>"
  exit 1
fi
package_name=$package

#the full list of the platforms: https://golang.org/doc/install/source#environment
platforms=(
"darwin/amd64"
# "darwin/arm64"
 )

for platform in "${platforms[@]}"
do
    platform_split=(${platform//\// })
    GOOS=${platform_split[0]}
    GOARCH=${platform_split[1]}
    # output_name="../compiled/"$package_name'-'$GOARCH
    output_name="../"$package_name
    if [ $GOOS = "windows" ]; then
        output_name+='.exe'
    fi

    (
        env GOOS=$GOOS GOARCH=$GOARCH GODEBUG=netdns=cgo+2 CGO_ENABLED=0 go build -ldflags "-s -w" -o $output_name .;
        upx --best --lzma $output_name
    )
    if [ $? -ne 0 ]; then
        echo 'An error has occurred! Aborting the script execution...'
        exit 1
    fi
done