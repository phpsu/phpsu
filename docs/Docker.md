# Docker usage with phpsu

You can use phpsu's Docker image if you like.

## Advantages, or why should I use the Docker image:

- you don't need to use composer it is all bundled within the Docker image

## Limitations, or why shouldn't I use the Docker image?:

- you should understand docker networking
- your local composer autoloading is not working as phpsu is installed globally inside the container.
- if needed: you need to add all your necessary environment variables to the container

## Basic Usage

The Image has phpsu and all it's dependencies installed.
There's just a few things you need to get started:
- if needed: your ssh key needs to be inside the container.  
  It should be in the directory `/home/phpsu/.ssh/`, we recommend to use a volume like this:  
  ``-v ~/.ssh/:/home/phpsu/.ssh/``
- your `phpsu-config.php` needs to be inside the container  
  It should be in the directory `/app/`, we recommend to use a volume like this:  
  ``-v $(pwd):/app/``
- if needed: your user id and group id needs to be inside the container  
  you should set it to your current user, we recommend to use the user like this:  
  ``-u $(id -u):$(id -g)``
  
The basic command to use phpsu's Docker image looks like this:

``docker run --rm -it -u $(id -u):$(id -g) -v ~/.ssh/:/home/phpsu/.ssh/ -v $(pwd):/app/ phpsu/phpsu:2.2.0 phpsu ssh production``

If you use this command very often, we recommend setting up an alias like this one:
``alias phpsu='docker run --rm -it -u $(id -u):$(id -g) -v ~/.ssh/:/home/phpsu/.ssh/ -v $(pwd):/app/ phpsu/phpsu:2.2.0 phpsu'``

## Long running container inside your docker-compose.yml

docker-compose.yml
````yml
services:
  #... other services

  web:
    image: php:5.6
    volumes:
      - .:/app
      - /var/run/docker.sock:/var/run/docker.sock:ro
      #...

  phpsu:
    image: phpsu/phpsu:2.2.0
    volumes:
      - ./:/app
      - ~/.ssh:/home/phpsu/.ssh
    user: ${APPLICATION_UID:-1000}:${APPLICATION_GID:-1000}
    stop_signal: SIGKILL
    command: tail -f /dev/null
````

You can create an alias inside your web container like this for your phpsu command like this:  
put it inside your `~/.bashrc` of your web container and then you will be able to run phpsu "totally normal"
````bash
CONTAINER_ID=$(basename $(cat /proc/1/cpuset))
DOCKER_COMPOSE_PROJECT=$(sudo docker inspect ${CONTAINER_ID} | grep '"com.docker.compose.project":' | awk '{print $2}' | tr --delete '"' | tr --delete ',')
export PHPSU_CONTAINER=\$(sudo docker ps -f \"name=\${DOCKER_COMPOSE_PROJECT}_phpsu_1\" --format {{.Names}})
alias phpsu='docker exec -u $(id -u):$(id -g) -w $(pwd) -it ${PHPSU_CONTAINER} /bin/sh /phpsu/entrypoint.sh phpsu'
````
