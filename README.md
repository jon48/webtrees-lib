# webtrees-lib
Library to extend webtrees core capabilities

## Contents

* [License](#license)
* [Introduction](#introduction)
* [List of MyArtJaub modules](#list-of-myartjaub-modules)
* [General notes](#general-notes)
* [System requirements](#system-requirements)
* [Installation / Upgrading](#installation--upgrading)
* [Contacts](#contacts)

### License

* **webtrees-lib: MyArtJaub library for webtrees**
* Copyright (C) 2009 to 2016 Jonathan Jaubart.
* Derived from **webtrees** - Copyright (C) 2010 to 2014  webtrees development team.
* Derived from PhpGedView - Copyright (C) 2002 to 2010  PGV Development Team.

This program is free software; you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

See the GPL.txt included with this software for more detailed licensing
information.


### Introduction

Initially user of PhpGedView, I started developing some customisations and personal 
modules in 2009 in order either to fill some gaps in features or to adapt the software
to my liking. This is when the Rural theme was first created for instance.

When the main PGV developers moved to create **webtrees**, I slowly migrated my code 
to the new platform, taking advantage of the evolved architecture to refactor some of
the modules.

Following the further code evolutions in the version 1.7.0 of **webtrees**, I decided
to split the library part of my code from the main **webtrees-geneajaubart** package, 
as well as renaming the modules from the too generic Perso prefix, to a more *branded*
name: MyArtJaub (a rather bad pun on my surname...). 

My personal and professional constraints have not allowed me to provide the same level
of support as I used to, nevertheless I have always wished to share my changes 
with the general **webtrees** audience. I was maintaining an SVN repository on Assembla,
but since the migration of **webtrees** to Github, I have as well created the current
Git repositories.

Please read carefully the instructions below, as some modules need changes in the core
code, hence cannot be just added to a standard **webtrees** installation.

*Jonathan Jaubart*

### List of MyArtJaub modules

Mandatory modules:

* **MyArtJaub Hooks** (`myartjaub_hooks`)
  * Allows hooking MyArtJaub modules in core code more easily.

Available modules:

* **MyArtJaub Administrative Tasks** (`myartjaub_admintasks`)
  * Runs administrative tasks on a scheduled manner.
* **MyArtJaub Certificates** (`myartjaub_certificates`)
  * Alternative management of certificates supporting sources.
* **MyArtJaub Geographical Dispersion** (`myartjaub_geodispersion`)
  * Provide geographical dispersion analysis on Sosa ancestors.  
* **MyArtJaub Miscellaneous Extensions** (`myartjaub_misc`)
  * Placeholder module for miscellaneous extensions.
* **MyArtJaub Patronymic Lineage** (`myartjaub_patronymiclineage`)
  * Alternative to Branches page (created before the latter).
* **MyArtJaub Sources** (`myartjaub_issourced`)
  * Provides information about the level and quality of sourced for records.
* **MyArtJaub Sosa** (`myartjaub_sosa`)
  * Module to manage Sosa ancestors, and provide statistics.
* **MyArtJaub Welcome Block** (`myartjaub_welcome_block`)
  * Merge of standard welcome and login blocks, with display of Piwik statistics
  
### General notes

Please note that the modules are not translated directly in this library. Translations 
would be managed through the related module structure in the  **webtrees-geneajaubart**.

### System requirements

It is required to run PHP 5.4 to be able to run the **webtrees-lib** library.
Except the above, **webtrees-lib** shares the same requirements and system configuration as a standard **webtrees** installation.

### Installation / Upgrading

The **webtrees-lib** needs to be integrated to a container project, as a library, and cannot be run 
as a standalone application.

You can use the **webtrees-geneajaubart** project as a example of container project.

**webtrees-lib** can be installed and updated with the composer tool.

To install the library, run the command:

```
composer require jon48/webtrees-lib
```

You can as well add the following to your `composer.json` file:

   ``` json
   {
       "require": {
           "jon48/webtrees-lib": "*"
       }
   }
   ```

Then run the command:

```
composer install
```
	
In order to update the package, run the command:

```
composer update
```

### Contacts

General questions on the standard **webtrees** software should be addressed to the
[official forum](http://www.webtrees.net/index.php/forum)

You can contact the author (Jonathan Jaubart) of the **webtrees-lib** and **webtrees-geneajaubart**
projects through his personal [GeneaJaubart website](http://genea.jaubart.com/wt/) (link
at the bottom of the page).

