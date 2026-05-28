<?php

namespace AIDA\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Client Class
 */
class AI_Client {

	/**
	 * Send context and prompt to AI API.
	 */
	public static function get_diagnosis( $context, $user_problem = '' ) {
		$api_key  = get_option( 'aida_api_key' );
		$model    = get_option( 'aida_model', 'gpt-4-turbo-preview' );
		$base_url = get_option( 'aida_base_url', 'https://api.openai.com/v1' );

		if ( ! $api_key ) {
			return array( 'error' => 'کلید API تنظیم نشده است.' );
		}

		$prompt = self::build_prompt( $context, $user_problem );

		$endpoint = rtrim( $base_url, '/' ) . '/chat/completions';

		$response = wp_remote_post( $endpoint, array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'body'    => json_encode( array(
				'model'    => $model,
				'messages' => array(
					array(
						'role'    => 'system',
						'content' => 'You are a senior WordPress core developer and debugging expert. Provide diagnosis and fixes in Persian (Farsi). Use the provided context to be specific.'
					),
					array(
						'role'    => 'user',
						'content' => $prompt
					),
				),
				'temperature' => 0.7,
			) ),
		) );

		if ( is_wp_error( $response ) ) {
			return array( 'error' => $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['choices'][0]['message']['content'] ) ) {
			return array( 'success' => true, 'content' => $body['choices'][0]['message']['content'] );
		}

		return array( 'error' => 'پاسخ نامعتبر از API.' );
	}

	/**
	 * Test the API connection with a simple request.
	 */
	public static function test_connection( $custom_api_key = null, $custom_base_url = null, $custom_model = null ) {
		$api_key  = ! empty( $custom_api_key ) ? $custom_api_key : get_option( 'aida_api_key' );
		$model    = ! empty( $custom_model ) ? $custom_model : get_option( 'aida_model', 'gpt-4-turbo-preview' );
		$base_url = ! empty( $custom_base_url ) ? $custom_base_url : get_option( 'aida_base_url', 'https://api.openai.com/v1' );

		if ( ! $api_key ) {
			return array( 'error' => 'کلید API تنظیم نشده است.' );
		}

		$endpoint = rtrim( $base_url, '/' ) . '/chat/completions';

		$response = wp_remote_post( $endpoint, array(
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'body'    => json_encode( array(
				'model'    => $model,
				'messages' => array(
					array(
						'role'    => 'user',
						'content' => 'Say "OK" if you can hear me.'
					),
				),
				'max_tokens' => 5,
			) ),
		) );

		if ( is_wp_error( $response ) ) {
			return array( 
				'error' => 'خطای شبکه وردپرس: ' . $response->get_error_message() . ' (آدرس مقصد: ' . $endpoint . ')'
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			$body_raw = wp_remote_retrieve_body( $response );
			$body = json_decode( $body_raw, true );
			
			$msg = 'خطای سرور (کد: ' . $code . ')';
			if ( isset( $body['error']['message'] ) ) {
				$msg = $body['error']['message'];
			} elseif ( ! empty( $body_raw ) ) {
				$msg .= ' - پاسخ سرور: ' . substr( strip_tags( $body_raw ), 0, 200 );
			}

			return array( 'error' => $msg . ' (URL: ' . $endpoint . ')' );
		}

		return array( 'success' => true, 'url' => $endpoint );
	}

	/**
	 * Build prompt for AI.
	 */
	private static function build_prompt( $context, $user_problem ) {
		$prompt = "You are analyzing a WordPress site for errors. Here is the context:\n";
		$prompt .= wp_json_encode( $context, JSON_PRETTY_PRINT ) . "\n\n";
		
		if ( ! empty( $user_problem ) ) {
			$prompt .= "The user is reporting the following specific problem: " . $user_problem . "\n\n";
		}

		$prompt .= "CRITICAL INSTRUCTIONS:\n";
		$prompt .= "1. Your primary goal is to identify the EXACT plugin, theme, or file causing the main issue.\n";
		$prompt .= "2. Prioritize 'Fatal Error' and 'Warning' over 'Notice' or 'Deprecated' messages.\n";
		$prompt .= "3. If you see a log entry like 'AIDA_TEST_ERROR' or similar custom triggers, pay close attention to them as they are direct indicators of intentional tests.\n";
		$prompt .= "4. Look at the stack traces in the error logs to find the originating file.\n";
		$prompt .= "5. If multiple issues exist, focus on the one that most likely blocks site functionality (like plugin activation issues).\n\n";

		$prompt .= "Please provide your response in PERSIAN (Farsi) using these exact headers:\n";
		$prompt .= "### تشخیص مشکل:\n";
		$prompt .= "(Identify what is happening)\n\n";
		$prompt .= "### علت احتمالی:\n";
		$prompt .= "(Identify the culprit plugin/theme and why it's happening)\n\n";
		$prompt .= "### توضیح فنی:\n";
		$prompt .= "(Technical details for developers)\n\n";
		$prompt .= "### راه حل پیشنهادی:\n";
		$prompt .= "(How to fix it)\n\n";
		$prompt .= "### مراحل رفع مشکل:\n";
		$prompt .= "(Step-by-step instructions)\n\n";
		$prompt .= "Use markdown for bold text and code blocks.";

		return $prompt;
	}
}
