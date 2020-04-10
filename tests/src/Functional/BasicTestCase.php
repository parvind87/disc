<?php

namespace Drupal\Tests\disc\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test basic functionality of My Module.
 *
 * @group disc
 */
class BasicTestCase extends BrowserTestBase
{
    protected $defaultTheme = 'stable';
    /**
     * {@inheritdoc}
     */
    public static $modules = [
    // Module(s) for core functionality.
    'node',
    'views',

    // This custom module.
    'disc',
  ];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        // Make sure to complete the normal setup steps first.
        parent::setUp();

        // Set the front page to "node".
        \Drupal::configFactory()
      ->getEditable('system.site')
      ->set('page.front', '/node')
      ->save(true);
    }

    /**
     * Make sure the site still works. For now just check the front page.
     */
    // public function testTheSiteStillWorks() {
    //   // Load the front page.
    //   $this->drupalGet('<front>');

    //   // Confirm that the site didn't throw a server error or something else.
    //   $this->assertSession()->statusCodeEquals(200);

    //   // Confirm that the front page contains the standard text.
    //   $this->assertText('Welcome to Drupal');
    // }
    /**
     * Make sure the disc/list page works.
     */
    public function testDiscList()
    {
        $assert = $this->assertSession();
        $this->drupalLogin($this->createUser());
        $result = $this->container->get('disc.default')->load();
        // Test the example description page.
        // Load the Listing page.
        $this->drupalGet('/disc/list');
        // Confirm that the listing didn't throw a server error or something else.
        $this->assertSession()->statusCodeEquals(200);
        $assert->pageTextContains('Members List');
        $assert->pageTextMatches('%Name[th/<>\w\s]+Email%');

        // Add the new entry.
        $this->drupalPostForm(
        '/disc/add',
        [
        'name' => 'Corona',
        'email' => 'Corona@gmail.com',
        'address' => 'this is add'
      ],
        'Submit'
    );
        // Now find the new entry.
        $this->drupalGet('/disc/list');
        $assert->pageTextMatches('%Corona[td/<>\w\s]+Corona%');
    }
    public function testDiscLogoutAndPost()
    {
        // Try to add an entry while logged out.
        $this->drupalLogout();
        $this->drupalPostForm(
             '/disc/add',
             [
             'name' => 'Anonymous',
             'email' => '1@1.com',
             'address' => 'not a number',
           ],
             'Submit'
         );
        $assert->pageTextContains('You must be logged in to add values to the database.');
    }
    public function testListXpath()
    {
        $this->drupalGet('/disc/list');
        $rows = $this->xpath("//*[@id='disc-members-list']");
        $this->assertCount(1, $rows);

       

    }
}
