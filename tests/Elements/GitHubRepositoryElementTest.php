<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Tests\Elements;

use Clesson\Silverstripe\GitHub\Elements\GitHubRepositoryListElement;
use Clesson\Silverstripe\GitHub\Elements\GitHubRepositorySingleElement;
use Clesson\Silverstripe\GitHub\Models\GitHubRepository;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Dev\SapphireTest;

class GitHubRepositoryElementTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testListElementFieldsAndFiltering(): void
    {
        GitHubRepository::create(['Name' => 'alpha', 'FullName' => 'example/alpha'])->write();
        GitHubRepository::create(['Name' => 'beta', 'FullName' => 'example/beta'])->write();

        $element = GitHubRepositoryListElement::create();
        $this->assertInstanceOf(BaseElement::class, $element);
        $this->assertSame(0, $element->getFilteredRepositories()->count());
        $this->assertNotNull($element->getCMSFields()->dataFieldByName('FilterName'));

        $element->FilterName = 'alpha';
        $this->assertSame(1, $element->getFilteredRepositories()->count());
        $this->assertSame('example/alpha', $element->getFilteredRepositories()->first()->FullName);
    }

    public function testSingleElementUsesOneResult(): void
    {
        GitHubRepository::create(['Name' => 'alpha', 'FullName' => 'example/alpha'])->write();
        GitHubRepository::create(['Name' => 'alpine', 'FullName' => 'example/alpine'])->write();

        $element = GitHubRepositorySingleElement::create();
        $element->FilterName = 'al';
        $this->assertSame(1, $element->Limit);
        $this->assertNull($element->getCMSFields()->dataFieldByName('Limit'));
        $this->assertSame(1, $element->getFilteredRepositories()->count());
        $this->assertSame('example/alpha', $element->getFilteredRepository()->FullName);
        $this->assertSame('example/alpha', $element->getSummary());
    }
}
