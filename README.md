# QueryBus Bundle [![SensioLabsInsight](https://insight.sensiolabs.com/projects/6643197f-15e8-48c1-9631-86dd0a3547c3/mini.png)](https://insight.sensiolabs.com/projects/6643197f-15e8-48c1-9631-86dd0a3547c3) [![Travis CI](https://travis-ci.org/gnugat/query-bus-bundle.png)](https://travis-ci.org/gnugat/query-bus-bundle)

[QueryBus](http://gnugat.github.io/query-bus) integration in [Symfony](http://symfony.com).

## Installation

QueryBusBundle can be installed using [Composer](http://getcomposer.org/):

    composer require "gnugat/query-bus-bundle:~1.0"

We then need to register it in our application:

```php
<?php
// File: app/AppKernel.php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Gnugat\QueryBusBundle\GnugatQueryBusBundle(),
        );
        // ...
    }

    // ...
}
```

## Usage example

Let's take the following entity:

```php
<?php
// File: src/AppBundle/Entity/Article.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="article")
 */
class Article
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    public function __construct($title, $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContent()
    {
        return $this->content;
    }
}
```

In order to get one article by ID using QueryBundle, we have first to create an
[Interrogatory Message](http://verraes.net/2015/01/messaging-flavours/):

```php
<?php
// File: src/AppBundle/QueryBus/GetArticle.php

namespace AppBundle\QueryBus;

class GetArticle
{
    public $id;

    public function __construct($id)
    {
        if (null === $id) {
            throw new \InvalidArgumentException('Missing required argument: ID');
        }
        $this->id = $id;
    }
}
```

We then have to create a `QueryMatcher`:

```php
<?php
// File: src/AppBundle/Marshaller/ArticleMarshaller.php

namespace AppBundle\QueryBus;

use AppBundle\Entity\Article;
use Doctrine\Common\Persistence\ObjectManager;
use Gnugat\QueryBus\QueryMatcher;

class GetArticleMatcher implements QueryMatcher
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function supports($query)
    {
        return $query instanceof GetArticle;
    }

    public function match($query)
    {
        $article = $this->objectManager->find('AppBundle:Article', $query->id);
        if (null === $article) {
            throw new \DomainException(sprintf('Could not find article for ID "%s"', $query->id));
        }

        return $article;
    }
}
```

The next step is to define it as a service:

```
# File: app/config/services.yml
services:
    app.get_article_matcher:
        class: AppBundle\QueryBus\GetArticleMatcher
        tags:
            - { name: gnugat_query_bus.query_matcher }
```

> **Note**: Thanks to the ` gnugat_query_bus.query_matcher` tag, the `GetArticleMatcher`
> will be registered in the main `gnugat_query_bus.query_bus` service.

Finally we can request the article:

```php
<?php
// File: src/AppBundle/Controller/ArticleController.php

namespace AppBundle\Controller;

use AppBundle\QueryBus\GetArticle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ArtcileController extends Controller
{
    /**
     * @Route("/api/v1/articles/{id}")
     * @Method({"GET"})
     */
    public function viewAction($id)
    {
        $article = $this->get('gnugat_query_bus.query_bus')->match(new GetArticle($id));

        return new JsonResponse(array(
            'id' => $article->getId(),
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
        ), 200);
    }
}
```

## Further documentation

You can see the current and past versions using one of the following:

* the `git tag` command
* the [releases page on Github](https://github.com/gnugat/query-bus-bundle/releases)
* the file listing the [changes between versions](CHANGELOG.md)

You can find more documentation at the following links:

* [copyright and MIT license](LICENSE)
* [versioning and branching models](VERSIONING.md)
* [contribution instructions](CONTRIBUTING.md)
