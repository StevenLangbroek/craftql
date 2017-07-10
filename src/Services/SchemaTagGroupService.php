<?php

namespace markhuot\CraftQL\Services;

use Craft;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use markhuot\CraftQL\Plugin;
use yii\base\Component;

class SchemaTagGroupService extends Component {

  public $groups = [];
  private $fields;

  function __construct(
    \markhuot\CraftQL\Services\FieldService $fields
  ) {
    $this->fields = $fields;
  }

  function loadAllGroups() {
    foreach (Craft::$app->tags->allTagGroups as $group) {
      $this->groups[$group->id] = $this->parseGroupToObject($group);
    }
  }

  function getGroup($groupId) {
    if (!isset($this->groups[$groupId])) {
      $group = Craft::$app->tags->getTagGroupById($groupId);
      $this->groups[$groupId] = $this->parseGroupToObject($group);
    }

    return $this->groups[$groupId];
  }

  function parseGroupToObject($group) {
    $tagGroupFields = [];
    $tagGroupFields['id'] = ['type' => Type::int()];
    $tagGroupFields['title'] = ['type' => Type::string()];
    $tagGroupFields = array_merge($tagGroupFields, $this->fields->getFields($group->fieldLayoutId));

    return new ObjectType([
      'name' => ucfirst($group->handle).'Tags',
      'fields' => $tagGroupFields,
    ]);
  }

}
