UPGRADE FROM 3.x to 4.0
=======================

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](https://github.com/sonata-project/SonataNewsBundle/tree/3.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataNewsBundle/compare/3.x...4.0.0).

## CommentManager/PostManager (document and entity)

If you have implemented a custom manager, you must adapt the signature of `getPager` method to return
a `PagerInterface`

## PostManager

If you have implemented a custom post manager, you must adapt the signature of the following new methods to match the one in `PostManagerInterface` again:
 * `findOneByPermalink`
 * `getPager`
 * `getPublicationDateQueryParts`

If you are using mongodb, you have to use `PostManager::findOneBySlug` instead of `PostManager::findOneByPermalink`.

## Controllers

If you have extended a controller, they are now extending `Symfony\Bundle\FrameworkBundle\Controller\AbstractController` instead of deprecated
`Symfony\Bundle\FrameworkBundle\Controller\Controller`
