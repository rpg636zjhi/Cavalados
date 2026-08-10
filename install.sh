#!/bin/bash

sudo apt-get update
sudo apt-get upgrade -y

sudo apt-get install unzip

unzip binphp7.zip
unlink binphp7.zip

sudo chmod 777 ./start.sh

echo "Instalação realizada com sucesso, use \"./start.sh\"!"
