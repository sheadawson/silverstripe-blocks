# Under Development.

## Requirements

* SilverStripe CMS 3.1
* [GridFieldExtensions](https://github.com/ajshort/silverstripe-gridfieldextensions)
* [MultivalueField](https://github.com/nyeholt/silverstripe-multivaluefield)


## Installation

#### Composer

	composer require sheadawson/silverstripe-blocks
	
Install via composer, run dev/build

## Quickstart

### Define Block Areas for your theme in mysite/_config/config.yml

``` yml
BlockManager:
  themes:
    simple:
      areas:
        Sidebar: true # a Sidebar area will be available on all page types in simple theme
        BeforeContent:
          only: HomePage # a BeforeContent area will be available only on HomePage page types in simple theme
        AfterContent:
          except: HomePage # a AfterContent area will be available on all page types except HomePage in simple theme
        Footer: true # a Footer area will be available on all page types in simple theme
```

### Add Block Areas to your themes templates

For example, adding the BeforeContent and AfterContent blocks would look something like

```html
<article>
	<h1>$Title</h1>
	$BlockArea(BeforeContent)
	<div class="content">$Content</div>
	$BlockArea(AfterContent)
</article>
```

$BlockArea(BeforeContent) will loop over and display all blocks assigned to the BeforeContent area on the current page

### Add Blocks to a page in the CMS

You will now be able to add Blocks to Pages and "Global Blocks" to SiteConfig(TODO) (or Sites if using [Multisites](https://github.com/sheadawson/silverstripe-multisites)). You can also define "BlockSets" in the Blocks model admin. BlockSets can be used to apply a common collection of blocks to pages that match the criteria you define on the set.


#### Restrict Blocks to viewer groups or logged in users

When editing a block, you can restrict who can see it in the frontend by selecting "logged in users" or "users from these groups" under the Viewer Groups tab.

#### Ordering blocks

Each block has a "Weight" attribute. Set a large value to Sink or high value to float.



## TODO

* Dev - Forms inside Blocks (BlockController?)
* Dev - Ability to limit a block area to a maximum number of blocks
* User - Ability to view site with BlockAreas mapped/highlighted/labeled
* Write Tests