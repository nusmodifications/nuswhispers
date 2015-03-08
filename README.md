# NUS Whispers

Laravel 5 + AngularJS setup. Development environment powered by [Laravel Homestead](https://github.com/laravel/homestead) (modified).

## Requirements
* [Vagrant](http://www.vagrantup.com)
* [VirtualBox](https://www.virtualbox.org/)
* [vagrant-hostmanager](https://github.com/smdahlen/vagrant-hostmanager)

## Setting Up
* Install [Vagrant](http://www.vagrantup.com) and [VirtualBox](https://www.virtualbox.org/) if you have not done so.

* Install *vagrant-hostmanager* by running the following command:
    ```bash
    $ vagrant plugin install vagrant-hostmanager
    ```

* Run the development environment by running the following command:
    ```bash
    $ vagrant up
    ```

* After you have regained control of your terminal, SSH the virtual terminal by running:
    ```bash
    $ vagrant ssh
    ```

* Run *npm* and *bower* to ensure that the dependencies are installed by running:
    ```bash
    $ cd /home/vagrant/sites/nuswhispers
    $ npm install
    $ bower install
    ```
* If everything is running perfectly, you should be able to access the website by requesting http://nuswhispers.local with your web browser.

## Development
If you are developing the frontend (AngularJS), run *gulp* to automatically compile the scripts:
```bash
$ cd /home/vagrant/sites/nuswhispers
$ gulp
```

## TODO
* Automate the last two steps.

