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
- your ssh key needs to be inside the container.  
  It should be in the directory `/root/.ssh/`, we recommend to use a volume like this:  
  ``-v ~/.ssh/:/root/.ssh/``
- your `phpsu-config.php`needs to be inside the container  
  It should be in the directory `/app/`, we recommend to use a volume like this:  
  ``-v $(pwd):/app/``
  
The basic command to use phpsu's Docker image looks like this:

``docker run --rm -it -v ~/.ssh/:/root/.ssh/ -v $(pwd):/app/ phpsu/phpsu:1.1.0 phpsu ssh production``

If you use this command very often, we recommend to setup an alias like this one:
``alias phpsu='docker run --rm -it -v ~/.ssh/:/root/.ssh/ -v $(pwd):/app/ phpsu/phpsu:1.1.0 phpsu'`` 
