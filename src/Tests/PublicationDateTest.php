<?php

/**
 * @file
 * Contains \Drupal\publication_date\Tests\PublicationDateTest.
 */

namespace Drupal\publication_date\Tests;

use Drupal\node\Entity\NodeType;
use Drupal\simpletest\WebTestBase;

/**
 * Tests for publication_date.
 *
 * @group publication_date
 */
class PublicationDateTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'user', 'publication_date');

  protected $privileged_user;

  public function setUp() {
    parent::setUp();

    NodeType::create([
      'type' => 'page',
      'name' => 'Page',
    ])->save();

    $this->privileged_user = $this->drupalCreateUser(array(
      'create page content',
      'edit own page content',
      'administer nodes',
      'set page published on date',
    ));
    $this->drupalLogin($this->privileged_user);
  }

  /**
   * Test automatic saving of variables.
   */
  public function testActionSaving() {

    // Create node to edit.
    $node = $this->drupalCreateNode(array('status' => 0));
    $unpublished_node = node_load($node->id());
    $this->assertTrue(empty($unpublished_node->published_at->value), 'Published date is initially empty');
    $this->assertTrue($unpublished_node->published_at->published_at_or_now == REQUEST_TIME,
      'Published at or now date is REQUEST_TIME');

    // Publish the node.
    $unpublished_node->status = 1;
    $unpublished_node->save();
    $published_node = node_load($node->id());
    $this->assertTrue(is_numeric($published_node->published_at->value),
      'Published date is integer/numberic once published');
    $this->assertTrue($published_node->published_at->value == REQUEST_TIME,
      'Published date is REQUEST_TIME');
    $this->assertTrue($unpublished_node->published_at->published_at_or_now == $published_node->published_at->value,
      'Published at or now date equals published date');

    // Remember time.
    $time = $published_node->published_at->value;

    // Unpublish the node and check that the field value is maintained.
    $published_node->status = 0;
    $published_node->save();
    $unpublished_node = node_load($node->id());
    $this->assertTrue($unpublished_node->published_at->value == $time,
      'Published date is maintained when unpublished');

    // Set the field to zero and and make sure the published date is empty.
    $unpublished_node->published_at->value = 0;
    $unpublished_node->save();
    $unpublished_node = node_load($node->id());
    $this->assertTrue(empty($unpublished_node->published_at->value),
      'Published date is empty when reset');

    // Set a custom time and make sure that it is saved.
    $time = $unpublished_node->published_at->value = 122630400;
    $unpublished_node->save();
    $unpublished_node = node_load($node->id());
    $this->assertTrue($unpublished_node->published_at->value == $time,
      'Custom published date is saved');
    $this->assertTrue($unpublished_node->published_at->published_at_or_now == $time,
      'Published at or now date equals published date');

    // Republish the node and check that the field value is maintained.
    $unpublished_node->status = 1;
    $unpublished_node->save();
    $published_node = node_load($node->id());
    $this->assertTrue($published_node->published_at->value == $time,
      'Custom published date is maintained when republished');

    // Set the field to zero and and make sure the published date is reset.
    $published_node->published_at->value = 0;
    $published_node->save();
    $published_node = node_load($node->id());
    $this->assertTrue($published_node->published_at->value > $time,
      'Published date is reset');

    // Now try it by purely pushing the forms around.

  }

  /**
   * Test automatic saving of variables via forms
   */
  public function testActionSavingOnForms() {
    $langcode = LANGUAGE_NONE;

    $edit = array();
    $edit["title"] = 'publication test node ' . $this->randomName(10);
    $edit["body[$langcode][0][value]"] = 'publication node test body ' . $this->randomName(32) . ' ' . $this->randomName(32);
    $edit['status'] = 1;

    // Hard to test created time == REQUEST_TIME because simpletest launches a
    // new HTTP session, so just check it's set.
    $this->drupalPost('node/add/page', $edit, t('Save'));
    $node = $this->drupalGetNodeByTitle($edit['title']);
    $this->drupalGet('node/' . $node->id() . '/edit');
    $value = $this->_getPubdateFieldValue();

    // Make sure it was created with Published At set.
    $this->assertNotNull($value, t('Publication date set initially'));

    // Unpublish the node and check that the field value is maintained.
    $edit['status'] = 0;
    $this->drupalPost('node/' . $node->id() . '/edit', $edit, t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertFieldByName('pubdate', $value,
      t('Pubdate is maintained when unpublished'));

    // Republish the node and check that the field value is maintained.
    $edit['status'] = 1;
    $this->drupalPost('node/' . $node->id() . '/edit', $edit, t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertFieldByName('pubdate', $value,
      t('Pubdate is maintained when republished'));

    // Set a custom time and make sure that it is stored correctly.
    $ctime = REQUEST_TIME - 180;
    $edit['pubdate'] = format_date($ctime, 'custom', 'Y-m-d H:i:s O');
    $this->drupalPost('node/' . $node->id() . '/edit', $edit, t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $custom_value = $this->_getPubdateFieldValue();
    $this->assertTrue($custom_value == format_date($ctime, 'custom',
        'Y-m-d H:i:s O'), t('Custom time/date was set'));

    // Set the field to empty and and make sure the published date is reset.
    $edit['pubdate'] = '';
    sleep(2);
    $this->drupalPost('node/' . $node->id() . '/edit', $edit, t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $new_value = $this->_getPubdateFieldValue();
    $this->assertNotNull($new_value,
      t('Published time was set automatically when there was no value entered'));
    $this->assertNotNull($new_value != $custom_value,
      t('The new published-at time is different from the custom time'));
    $this->assertTrue($new_value > $value,
      t('The new published-at time is greater than the original one'));

    // Unpublish the node.
    $edit['status'] = 0;
    $this->drupalPost('node/' . $node->id() . '/edit', $edit, t('Save'));

    // Set the field to empty and and make sure that it stays empty.
    $edit['pubdate'] = '';
    $this->drupalPost('node/' . $node->id() . '/edit', $edit, t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertFieldByName('pubdate', '',
      t('Publication date field is empty'));
  }

  // Test that it cares about setting the published_at field.
  // This is useful for people using 'migrate' etc.
  public function testActionSavingSetDate() {
    $node = $this->drupalCreateNode(array('status' => 0));
    $unpublished_node = node_load($node->id());
    $this->assertTrue(empty($unpublished_node->published_at->value),
      'Published date is initially empty');

    // Now publish this with our custom time...
    $unpublished_node->status = 1;
    $static_time = 12345678;
    $unpublished_node->published_at->value = $static_time;
    $unpublished_node->save();
    $published_node = node_load($node->id());
    // ...and see if it comes back with it correctly.
    $this->assertTrue(is_numeric($published_node->published_at->value),
      'Published date is integer/numberic once published');
    $this->assertTrue($published_node->published_at->value == $static_time,
      'Published date is set to what we expected');
  }

  /**
   * Returns the value of our published-at field
   * @return string
   */
  private function _getPubdateFieldValue() {
    $value = '';

    if ($this->assertField('pubdate', t('Published At field exists'))) {
      $field = $this->xpath('//input[@id="edit-pubdate"]');
      $value = (string) $field[0]['value'];
      return $value;
    }

    return $value;
  }

}
