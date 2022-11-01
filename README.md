# Custom Category API

To update in php 7.4
```
/usr/local/bin/ea-php74 ../composer.phar require nailalliance/colorcategory:dev-main
```

## To Install

Add to composer.json

```
bin/magento module:enable Nailalliance_Colorcategory
bin/magento setup:upgrade
```

```JSON
    "repositories": {
        "0": {
            "type": "composer",
            "url": "https://repo.magento.com/"
        },
        "nailalliance-colorcategory": {
            "type": "git",
            "url": "https://github.com/nail-alliance/colorcategory.git"
        }
    }
```

