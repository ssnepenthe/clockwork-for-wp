<?php

namespace Clockwork_For_Wp\Tests;

use Symfony\Component\HttpClient\Exception\TransportException;

function fixture_path( $file ) {
	return __DIR__ . "/fixtures/{$file}";
}

class Api {
	protected $ajax_url;
	protected $client;

	public function __construct( $client ) {
		$this->client = $client;
	}

	public function ajax_url() {
		if ( ! is_string( $this->ajax_url ) ) {
			try {
				$this->ajax_url = trim(
					$this->client
						->request( 'GET', '/?enable=0' )
						->filter( '#cfw-coh-ajaxurl' )
						->text( '' )
				);
			} catch ( TransportException $e ) {
				$this->ajax_url = '';
			}
		}

		return $this->ajax_url;
	}

	public function clean_metadata() {
		$this->client->request(
			'GET',
			"{$this->ajax_url()}?action=cfw_coh_clean_metadata"
		);
	}

	public function is_available() {
		return '' !== $this->ajax_url();
	}

	public function metadata_count() {
		$this->client->request(
			'GET',
			"{$this->ajax_url()}?action=cfw_coh_metadata_count"
		);

		return json_decode( $this->client->getResponse()->getContent(), true )['data'];
	}

	public function metadata_by_id( $id ) {
		$this->client->request(
			'GET',
			"{$this->ajax_url()}?action=cfw_coh_metadata_by_id&id={$id}"
		);

		return json_decode( $this->client->getResponse()->getContent(), true )['data'];
	}
}
