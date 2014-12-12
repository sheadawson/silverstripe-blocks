# Dev version ()

## Todo/Features development

- [x] Duplication of Blocks in BlockAdmin
- [ ] Re-add: Sorting primarily by Area (in order of declaration in config), on Pages (removed in favor of dr'ndr sorting)
- [x] Allow deletion of blocks from BlockAdmin
- [x] Replacing Title as standard field with Name, as Title will often be used for actual content
- [x] Name can function as a description for finding/selecting the block later
- [x] Block->Area (->BlockArea) managed on many_many_extraFields (on relations Page)
- [ ] Block->Area (->BlockArea) managed on many_many_extraFields (on relations SiteConfig, Blocksets)
- [x] Block->Weight (->Sort) managed on many_many_extraFields (on relations Page)
- [ ] Block->Weight (->Sort) managed on many_many_extraFields (on relations SiteConfig, Blocksets)
- [ ] Ability to specify Above or Below on global/blockset blocks many_many_extraFields to determine where they should sit with the page specific blocks.
- [x] Allow Sorting by drag & drop on a page (may interfere with primarily sorting by Area)
- [ ] TODO: combine merges with Sort from OrderableRows in BlocksSiteTreeExtension::getBlockList()
- [ ] Add icon/pic to base Block as method of recognition when dealing with lots of different blocks
- [x] Show 'used on pages' in BlockAdmin
- [x] Allow editing of related pages from Block (requires gridfieldsitetreebuttons)
- [x] Remove editablecolumns from BlockAdmin (no use, cannot be saved)
- [ ] Forms (userforms/flexiforms) integration or at least some documentation/samples
- [ ] Versioning (basic), maybe via betterbuttons (instead of versionedgridfield)
- [x] Make block Blocksets & global blocks functionality & interfaces optional via config
- [ ] TODO: check sets & blocks optional functionality on page-output if old relations are still in place

## Requirements

* SilverStripe CMS 3.1
* [GridFieldExtensions](https://github.com/silverstripe-australia/silverstripe-gridfieldextensions)
* [MultivalueField](https://github.com/nyeholt/silverstripe-multivaluefield)

### New requirements

### Recommended
* [GridField Copybutton](https://github.com/unisolutions/silverstripe-copybutton) (duplication of blocks, from BlockAdmin)
* [GridField BetterButtons](https://github.com/unclecheese/silverstripe-gridfield-betterbuttons) (user friendly buttons & simple versioning (todo))
* [GridField SitetreeButtons](https://github.com/micschk/silverstripe-gridfieldsitetreebuttons) (edit related pages directly from block)


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

      use_global_blocks: false # Whether to use SiteConfig Blocks functionality (default if undeclared: true)
      use_blocksets: false # Whether to use BlockSet functionality (default if undeclared: true)

```

Remember to run ?flush=1 after modifying your .yml config to make sure it gets applied.

### Add Block Areas to your themes templates

Adding the BeforeContent and AfterContent blocks would look something like

```html
<article>
	<h1>$Title</h1>
	$BlockArea(BeforeContent)
	<div class="content">$Content</div>
	$BlockArea(AfterContent)
</article>
```

$BlockArea(BeforeContent) will loop over and display all blocks assigned to the BeforeContent area on the current page

You can limit a block area to a maximum number of blocks using the second limit parameter

```html
<article>
	$BlockArea(NewsBlocks, 3)
</article>
```

### Add Blocks to a page in the CMS

You will now be able to add Blocks to Pages and "Global Blocks" to SiteConfig (or Sites if using [Multisites](https://github.com/sheadawson/silverstripe-multisites)). You can also define "BlockSets" in the Blocks model admin. BlockSets can be used to apply a common collection of blocks to pages that match the criteria you define on the set.

#### ContentBlock Example
```php
class ContentBlock extends Block{

	private static $singular_name = 'Content Block';
	private static $plural_name = 'Content Blocks';

	private static $db = array(
		'Title' => 'Varchar(255)',
		'Content' => 'HTMLText'
	);


	public function getCMSFields(){
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab("Root.Main", new HeaderField('ContentStart', 'Block content'),'Title');

		return $fields;
	}

}
```

templates/includes/ContentBlock.ss

```html
<div class='$CSSClasses'>
	<div class='block_title'><h3>$Title</h3></div>
	<div class='block_content'>$Content</div>
</div>
```

#### Restrict Blocks to viewer groups or logged in users

When editing a block, you can restrict who can see it in the frontend by selecting "logged in users" or "users from these groups" under the Viewer Groups tab.

#### Ordering blocks

Each block has a "Weight" attribute. Set a big value to Sink or small value to float.

### Templates

There are 2 types of templates you should be aware of. 

#### BlockArea Template

The BlockArea template is responsible for looping over and rendering all blocks in that area. You can override this by creating a copy of the default BlockArea.ss and placing it in your theme's templates/Includes folder. 

It's likely that your block areas may require different templates. You can achieve this by creating a BlockArea_{AreaName}.ss template. 

#### Block Template

Each subclass of Block requires it's own template with the same name as the class. So, SlideshowBlock.php would have a SlideshowBlock.ss template. If your block requires different templates depending on the BlockArea it's in, you can create SlideshowBlock_{AreaName}.ss

### Block Area Preview

To aid website admins in identifying the areas they can apply blocks to, a "Preview Block Areas for this page" button is available in the cms. This opens the frontend view of the page in a new tab with ?block_preview=1. In Block Preview mode, Block Areas in the template are highlighted and labeled. 

There is some markup required in your BlockArea templates to facilitate this: The css class "block-area" and the data-areaid='$AreaID' attribute.

```html
<div class='block-area' data-areaid='$AreaID'>
	<% loop BlockArea %>
		$BlockHTML
	<% end_loop %>
</div>
```

### Screenshots

![](docs/images/overview.png)
Overview

![](docs/images/preview.png)
Preview of block locations

![](docs/images/edit.png)
Edit a block

![](docs/images/existing.png)
Add an existing block

## TODO

* Dev - Forms inside Blocks (BlockController?)
* Write Tests
