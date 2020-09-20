DPKG Browser
-------------
* Written by Oliver Lillie <buggedcom@gmail.com>    
* CV - [https://www.dropbox.com/s/e60phexa2mgazty/oliver-lillie-cv-and-projects.pdf?dl=0](https://www.dropbox.com/s/e60phexa2mgazty/oliver-lillie-cv-and-projects.pdf?dl=0)
* LinkedIn - [https://linkedin.com/in/oliverlillie/](https://linkedin.com/in/oliverlillie/)
* This package - [https://www.dropbox.com/sh/v2y4bitxgqtkgna/AACYtbMCcEfpHGlZeUP_HeIfa?dl=0](https://www.dropbox.com/sh/v2y4bitxgqtkgna/AACYtbMCcEfpHGlZeUP_HeIfa?dl=0)

Brief
-------------
On a Debian or an Ubuntu system there is a file called /var/lib/dpkg/status that holds information about software packages that the system knows about. Write a small program in a language of your choosing that exposes some key information about currently installed packages via an HTML interface. The program should listen to HTTP requests on port 8080 on localhost and provide the following features:

- The index page lists installed packages alphabetically with package names as links.
- When following each link, you arrive at an information about a single package. The following information should be included:
- Name
- Description
- The names of the packages the current package depends on (skip version numbers)
- The names of the packages that depend on the current package
- The dependencies and reverse dependencies should be clickable and the user can navigate the package structure by clicking from package to package.

Some things to keep in mind:
- Minimize the use of external dependencies.
- The goal of the assignment is to view how you solve the problems with the programming language, not how well you use package managers :)
- The main design goal of this program is maintainability.
- Only look at the Depends field. Ignore other fields that works kind of similarly, such as Suggests and Recommends.
- Sometimes there are alternates in a dependency list, separated by the pipe character (|). When rendering such dependencies, render any alternative that maps to a package name that have an entry in the status file as a link and just print the name of the package name for other packages.
- The section Syntax of control files of the Debian Policy Manual applies to the input data.
- A sample input file from https://gist.github.com/lauripiispanen/29735158335170c27297422a22b48caa (same as attached file in .zip)
- Please avoid making your submission available to potential third parties by posting it to an open service such as GitHub.
- We'd like you to demonstrate idiomatic style for the selected programming language, so please do take the time to familiarize yourself with the chosen language.

Prerequisites
-------------
Vagrant and Virtualbox are required for this demo.

* `Vagrant` - [https://www.vagrantup.com/downloads.html](https://www.vagrantup.com/downloads.html)
* `Virtualbox` - [https://www.virtualbox.org/wiki/Downloads](https://www.virtualbox.org/wiki/Downloads)

Quickstart
-------------
```
cd ~/path/to/directory/
vagrant up
```

After vagrant has completed you should see a box with final instructions.

However for completeness you can find the Vue.js frontend working on PHP and Node simulatoneously. 

* NodeJS - http://192.168.33.10:8080  
* PHP    - http://192.168.33.10:80    

If you are on Node, you can switch to PHP and visa versa by toggling on the switch in the footer of the app. The process is transparent and there is no difference in the users experience of the app, it is simply calling a different backend.

Brief Overview
-------------
* Frontend - `Vue.js`
* Backend  - `Nginx` with `NodeJS` - or - `PHP`

Why have I written two backends you ask? Simply it's a challenge. 

I initially wrote the `PHP` backend as it is in my main wheelhouse. But it was a bit too easy to do and I felt like I wanted more of a challenge so I set about learning `NodeJS` and porting the `PHP` into `Node`.

It was an interesting challenge, however since I've been working with ECMAScript/Javascript for over 17 years it wasn't really that much hard work and I found it relatively easy to work within `Node`.

The frontend again was a new challenge. In my current company we are stuck with legacy `DOJO` code which again doesn't really have too much demand, so therefore wanted to try my hand at something I have never tried before and therefore I decided upon `Vue.js` instead of `React`.

This was much more of a challenge than learning `Node`, however I found Vue much more rigedly structured than React and found it much more enjoyable to use. 

The Challenge
------------
As mentioned above the backend data parsing wasn't really that challenging and the solution essentially evoled around creating collections of packages and properties.

The real challege was utilising `Vue` for my vision of the package browser. Which was a package dependancy browser with hierarchical tree navigation so you can deep dive into dependancies with relative ease.

A large part of the time for the challenge actually came from making the UI aria accessible with full keyboard controls.

In Addition
-----------
If you are interested in seeing my work in Node and React you can view this project - [Terveystalo Lab Tests](https://www.dropbox.com/sh/9jucgxll841olwm/AADQf9qkJQxNVouvIHHxmJrxa?dl=0).