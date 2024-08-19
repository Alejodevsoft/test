<?php
/**
 * DowngradeRequestInformation
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  DocuSign\eSign
 * @author   Swagger Codegen team <apihelp@docusign.com>
 * @license  The DocuSign PHP Client SDK is licensed under the MIT License.
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * DocuSign REST API
 *
 * The DocuSign REST API provides you with a powerful, convenient, and simple Web services API for interacting with DocuSign.
 *
 * OpenAPI spec version: v2.1
 * Contact: devcenter@docusign.com
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 * Swagger Codegen version: 2.4.21
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace DocuSign\eSign\Model;

use \ArrayAccess;
use DocuSign\eSign\ObjectSerializer;

/**
 * DowngradeRequestInformation Class Doc Comment
 *
 * @category    Class
 * @package     DocuSign\eSign
 * @author      Swagger Codegen team <apihelp@docusign.com>
 * @license     The DocuSign PHP Client SDK is licensed under the MIT License.
 * @link        https://github.com/swagger-api/swagger-codegen
 */
class DowngradeRequestInformation implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'downgradeRequestInformation';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'downgrade_request_creation' => '?string',
        'downgrade_request_product_id' => '?string',
        'downgrade_request_status' => '?string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'downgrade_request_creation' => null,
        'downgrade_request_product_id' => null,
        'downgrade_request_status' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'downgrade_request_creation' => 'downgradeRequestCreation',
        'downgrade_request_product_id' => 'downgradeRequestProductId',
        'downgrade_request_status' => 'downgradeRequestStatus'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'downgrade_request_creation' => 'setDowngradeRequestCreation',
        'downgrade_request_product_id' => 'setDowngradeRequestProductId',
        'downgrade_request_status' => 'setDowngradeRequestStatus'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'downgrade_request_creation' => 'getDowngradeRequestCreation',
        'downgrade_request_product_id' => 'getDowngradeRequestProductId',
        'downgrade_request_status' => 'getDowngradeRequestStatus'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$swaggerModelName;
    }

    

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['downgrade_request_creation'] = isset($data['downgrade_request_creation']) ? $data['downgrade_request_creation'] : null;
        $this->container['downgrade_request_product_id'] = isset($data['downgrade_request_product_id']) ? $data['downgrade_request_product_id'] : null;
        $this->container['downgrade_request_status'] = isset($data['downgrade_request_status']) ? $data['downgrade_request_status'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets downgrade_request_creation
     *
     * @return ?string
     */
    public function getDowngradeRequestCreation()
    {
        return $this->container['downgrade_request_creation'];
    }

    /**
     * Sets downgrade_request_creation
     *
     * @param ?string $downgrade_request_creation 
     *
     * @return $this
     */
    public function setDowngradeRequestCreation($downgrade_request_creation)
    {
        $this->container['downgrade_request_creation'] = $downgrade_request_creation;

        return $this;
    }

    /**
     * Gets downgrade_request_product_id
     *
     * @return ?string
     */
    public function getDowngradeRequestProductId()
    {
        return $this->container['downgrade_request_product_id'];
    }

    /**
     * Sets downgrade_request_product_id
     *
     * @param ?string $downgrade_request_product_id 
     *
     * @return $this
     */
    public function setDowngradeRequestProductId($downgrade_request_product_id)
    {
        $this->container['downgrade_request_product_id'] = $downgrade_request_product_id;

        return $this;
    }

    /**
     * Gets downgrade_request_status
     *
     * @return ?string
     */
    public function getDowngradeRequestStatus()
    {
        return $this->container['downgrade_request_status'];
    }

    /**
     * Sets downgrade_request_status
     *
     * @param ?string $downgrade_request_status 
     *
     * @return $this
     */
    public function setDowngradeRequestStatus($downgrade_request_status)
    {
        $this->container['downgrade_request_status'] = $downgrade_request_status;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(
                ObjectSerializer::sanitizeForSerialization($this),
                JSON_PRETTY_PRINT
            );
        }

        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}

