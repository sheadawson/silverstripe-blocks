# Under Development.

## Quickstart

1. Add the module folder to your project

2. Define Block Areas for your theme in mysite/_config/config.yml


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

You will now be able to add Blocks to Pages, Sites if using Multisites. You can also define "BlockSets" in the Blocks model admin, blocks in these sets can be automatically inherited by any page that matched the BlockSets page criteria.



## TODO

* User - Ability to define "Global Blocks" on SiteConfig for non-multisite sites
* User - Ability to restrict BlockSets criteria to restrict to certain parent ids
* Dev - Forms inside Blocks (BlockController?)
* Dev - Ability to limit a block area to a maximum number of blocks
* User - Ability to view site with BlockAreas mapped/highlighted/labeled
* User - Ability to restrict viewing of blocks to specific groups/users 
* Write Tests