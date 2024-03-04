<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Routing;

use SimpleWpRouting\Responder\ComposableResponder;
use SimpleWpRouting\Responder\Partial\HeadersPartial;

final class File_Responder extends ComposableResponder {
	private string $path;

	public function __construct( string $path, string $mime ) {
		$this->path = $path;

		// @todo Are any other headers necessary?
		$this->getPartialSet()->get( HeadersPartial::class )->addHeaders( [
			'Content-Type' => $mime,
			'Content-Length' => \filesize( $this->path ),
		] );
	}

	public function on_template_redirect(): void {
		\readfile( $this->path );
		exit;
	}

	public function respond(): void {
		\add_action( 'template_redirect', [ $this, 'on_template_redirect' ] );

		parent::respond();
	}

	protected function createPartials(): array {
		return [
			new HeadersPartial(),
		];
	}
}
