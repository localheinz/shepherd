<?php

namespace Psalm\Spirit;

class Sender
{
	public static function send(array $github_data, array $psalm_data) : void
	{
		$config_path = '../config.json';

		if (!file_exists($config_path)) {
			throw new \UnexpectedValueException('Missing config');
		}

		$config = json_decode(file_get_contents($config_path), true);

		$repository = $github_data['repository']['full_name'];
		$pull_request_number = $github_data['pull_request']['number'];

		$git_hash = $psalm_data['git']['head']['id'];

		/** @var array<int, array{severity: string, line_from: int, line_to: int, type: string, message: string,
	     * 		file_name: string, file_path: string, snippet: string, from: int, to: int,
	     * 		snippet_from: int, snippet_to: int, column_from: int, column_to: int, selected_text: string}>
	     */
		$issues = $psalm_data['issues'];

		$file_comments = [];

		foreach ($issues as $issue) {
			if ($issue['severity'] === 'error') {
				$file_comments[] = [
					'path' => $issue['file_name'],
					'position' => $issue['line_from'],
					'body' => $issue['message'],
				];
			}
		}

		$client = new \Github\Client();
		$client->authenticate($config['user'], $config['password'], \Github\Client::AUTH_HTTP_PASSWORD);
		
		$repositories = $client
			->api('pull_request')
			->reviews()
			->create(
				$config['user'],
				$repository,
				$pull_request_number,
				[
					'commit_id' => $git_hash,
					'event' => 'COMMENT',
					'body' => 'Psalm has thoughts',
					'comments' => $file_comments,
				]
			);
	}
}