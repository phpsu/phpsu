# Docker usage with phpsu

You can use phpsu's Docker image if you like.

With the Docker image, you have some advantages, but also some limitations.

## Advantages, or why should I use the Docker image:

- you don't need to use composer it is all bundled within the Docker image

## Limitations, or why shouldn't I use the Docker image?:

- you should understand something about dockers networking
- you local composer autoloading is not working, as phpsu is installed globally inside the container.
- if needed: you need to give the container all your necessary Environment variables

## Basic Usage

The Image has phpsu and all it's necessary dependencies installed.
There's just a few things you need to get started:
- your ssh key needs to be inside the container.  
  It should lie in the directory `/root/.ssh/` we recommend to use a Docker volume like this:  
  ``-v ~/.ssh/:/root/.ssh/``
- your `phpsu-config.php`needs to be inside the container  
  It should lie in the directory `/app/` we recommend to use a Docker volume like this:  
  ``-v $(pwd):/app/``
  
So the basic command to use phpsu's Docker image looks like this:

``docker run --rm -it -v ~/.ssh/:/root/.ssh/ -v $(pwd):/app/ phpsu/phpsu:1.1.0 phpsu ssh production``

if you like you can use an Alias like this one:
``alias phpsu='docker run --rm -it -v ~/.ssh/:/root/.ssh/ -v $(pwd):/app/ phpsu/phpsu:1.1.0 phpsu'`` 
