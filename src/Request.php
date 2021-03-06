<?php

namespace markhuot\CraftQL;

class Request {

    private $token;
    private $entryTypes;
    private $volumes;
    private $categoryGroups;
    private $tagGroups;
    private $sections;

    function __construct($token) {
        $this->token = $token;
    }

    function addCategoryGroups($categoryGroups) {
        $this->categoryGroups = $categoryGroups;
    }

    function addTagGroups($tagGroups) {
        $this->tagGroups = $tagGroups;
    }

    function addEntryTypes($entryTypes) {
        $this->entryTypes = $entryTypes;
    }

    function addVolumes($volumes) {
        $this->volumes = $volumes;
    }

    function addSections($sections) {
        $this->sections = $sections;
    }

    function token() {
        return $this->token;
    }

    function categoryGroup($id) {
        return $this->categoryGroups->get($id);
    }

    function categoryGroups() {
        return $this->categoryGroups;
    }

    function tagGroup($id) {
        return $this->tagGroups->get($id);
    }
 
    function tagGroups() {
        return $this->tagGroups;
    }

    function entryTypes() {
        return $this->entryTypes;
    }

    function volumes() {
        return $this->volumes;
    }

    function sections() {
        return $this->sections;
    }

    function entriesCriteria($amount, $callback) {
        return function ($root, $args, $context, $info) use ($amount, $callback) {
            $criteria = $callback($root, $args, $context, $info);

            if (empty($args['section'])) {
                $args['sectionId'] = array_map(function ($value) {
                    return $value->value;
                }, $this->sections()->enum()->getValues());
            }
            else {
                $args['sectionId'] = $args['section'];
                unset($args['section']);
            }

            if (empty($args['type'])) {
                $args['typeId'] = array_map(function ($value) {
                    return $value->value;
                }, $this->entryTypes()->enum()->getValues());
            }
            else {
                $args['typeId'] = $args['type'];
                unset($args['type']);
            }
            
            foreach ($args as $key => $value) {
                $criteria = $criteria->{$key}($value);
            }

            if (!empty($info->fieldNodes)) {
                foreach ($info->fieldNodes[0]->selectionSet->selections as $selection) {
                    if (isset($selection->name->value) && $selection->name->value == 'author') {
                        $criteria->with('author');
                    }
                }
            }

            return $criteria->{$amount}();
        };
    }

}