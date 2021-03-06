<?php

namespace Drupal\Tests\image\Functional;

use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * TODO: Test the following functions.
 *
 * image.effects.inc:
 *   image_style_generate()
 *   \Drupal\image\ImageStyleInterface::createDerivative()
 *
 * image.module:
 *   image_style_options()
 *   \Drupal\image\ImageStyleInterface::flush()
 *   image_filter_keyword()
 */

/**
 * This class provides methods specifically for testing Image's field handling.
 */
abstract class ImageFieldTestBase extends BrowserTestBase {

  use ImageFieldCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'image', 'field_ui', 'image_module_test');

  /**
   * An user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  protected function setUp() {
    parent::setUp();

    // Create Basic page and Article node types.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));
      $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
    }

    $this->adminUser = $this->drupalCreateUser(array('access content', 'access administration pages', 'administer site configuration', 'administer content types', 'administer node fields', 'administer nodes', 'create article content', 'edit any article content', 'delete any article content', 'administer image styles', 'administer node display'));
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Preview an image in a node.
   *
   * @param \Drupal\Core\Image\ImageInterface $image
   *   A file object representing the image to upload.
   * @param string $field_name
   *   Name of the image field the image should be attached to.
   * @param string $type
   *   The type of node to create.
   */
  function previewNodeImage($image, $field_name, $type) {
    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
    );
    $edit['files[' . $field_name . '_0]'] = drupal_realpath($image->uri);
    $this->drupalPostForm('node/add/' . $type, $edit, t('Preview'));
  }

  /**
   * Upload an image to a node.
   *
   * @param $image
   *   A file object representing the image to upload.
   * @param $field_name
   *   Name of the image field the image should be attached to.
   * @param $type
   *   The type of node to create.
   * @param $alt
   *   The alt text for the image. Use if the field settings require alt text.
   */
  function uploadNodeImage($image, $field_name, $type, $alt = '') {
    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
    );
    $edit['files[' . $field_name . '_0]'] = drupal_realpath($image->uri);
    $this->drupalPostForm('node/add/' . $type, $edit, t('Save and publish'));
    if ($alt) {
      // Add alt text.
      $this->drupalPostForm(NULL, [$field_name . '[0][alt]' => $alt], t('Save and publish'));
    }

    // Retrieve ID of the newly created node from the current URL.
    $matches = array();
    preg_match('/node\/([0-9]+)/', $this->getUrl(), $matches);
    return isset($matches[1]) ? $matches[1] : FALSE;
  }

  /**
   * Retrieves the fid of the last inserted file.
   */
  protected function getLastFileId() {
    return (int) db_query('SELECT MAX(fid) FROM {file_managed}')->fetchField();
  }

}
