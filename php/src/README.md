# Building

* `docker buildx build -f php/src/54/Dockerfile php/. --platform linux/arm64,linux/amd64 --push --tag brandonkiefer/php-dev:5.4`
* `docker buildx build -f php/src/56/Dockerfile php/. --platform linux/arm64,linux/amd64 --push --tag brandonkiefer/php-dev:5.6`
* `docker buildx build -f php/src/72/Dockerfile php/. --platform linux/arm64,linux/amd64 --push --tag brandonkiefer/php-dev:7.2`
* `docker buildx build -f php/src/74/Dockerfile php/. --platform linux/arm64,linux/amd64 --push --tag brandonkiefer/php-dev:7.4`
