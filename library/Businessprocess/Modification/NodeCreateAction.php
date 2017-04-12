<?php

namespace Icinga\Module\Businessprocess\Modification;

use Icinga\Module\Businessprocess\BpConfig;
use Icinga\Module\Businessprocess\BpNode;
use Icinga\Module\Businessprocess\Node;
use Icinga\Exception\ConfigurationError;

class NodeCreateAction extends NodeAction
{
    /** @var string */
    protected $parentName;

    /** @var array */
    protected $properties = array();

    /** @var array */
    protected $preserveProperties = array('parentName', 'properties');

    /**
     * @param Node $name
     */
    public function setParent(Node $name)
    {
        $this->parentName = (string) $name;
    }

    /**
     * @return bool
     */
    public function hasParent()
    {
        return $this->parentName !== null;
    }

    /**
     * @return string
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * @param string $name
     */
    public function setParentName($name)
    {
        $this->parentName = $name;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     * @return $this
     */
    public function setProperties($properties)
    {
        $this->properties = (array) $properties;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function appliesTo(BpConfig $config)
    {
        return ! $config->hasNode($this->getNodeName());
    }

    /**
     * @inheritdoc
     */
    public function applyTo(BpConfig $config)
    {
        $name = $this->getNodeName();

        $properties = array(
            'name'        => $name,
            'operator'    => $this->properties['operator'],
        );
        if (array_key_exists('childNames', $this->properties)) {
            $properties['child_names'] = $this->properties['childNames'];
        } else {
            $properties['child_names'] = array();
        }
        $node = new BpNode($config, (object) $properties);

        foreach ($this->getProperties() as $key => $val) {
            if ($key === 'parentName') {
                try {
                    $config->getBpNode($val)->addChild($node);
                } catch (ConfigurationError $configError) {
                    throw $configError;
                }
                continue;
            }
            $func = 'set' . ucfirst($key);
            $node->$func($val);
        }

        $config->addNode($name, $node);

        return $node;
    }
}
