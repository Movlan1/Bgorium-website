<?php
namespace Melbahja\Seo\Schema;

use Melbahja\Seo\Interfaces\SchemaInterface;


/**
 * @package Melbahja\Seo
 * @since v2.0
 * @see https://git.io/phpseo
 * @license MIT
 * @copyright 2019-present Mohamed Elabhja
 */
class Thing implements SchemaInterface
{

	protected $type;
	protected $need_context;
	protected $data    = [];
	public    $context = null;


	public function __construct(string $type, array $data = [], bool $need_context = true)
	{
		$this->data = $data;
		$this->type = $type;
		$this->need_context = $need_context;
	}

	public function __get(string $name)
	{
		return $this->data[$name] ?? null;
	}


	public function __set(string $name, $value)
	{
		$this->data[$name] = $value;
	}

	public function jsonSerialize(): array
	{
		$data = [];

		if( $this->type ) $data['@type'] = $this->type;
		if( $this->need_context ) $data['@context'] = $this->context ?? "https://schema.org/";

		if( count($data) ) return array_merge($data, $this->data);
		else return $this->data;

	}

	public function __toString(): string
	{
		return '<script type="application/ld+json">'. json_encode($this) . '</script>';
	}
}
