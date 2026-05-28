<?php

namespace AIDA\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rule Engine Class
 */
class Rule_Engine {

	/**
	 * Analyze an error string against known rules.
	 */
	public static function analyze( $error_string ) {
		$rules = self::get_rules();

		foreach ( $rules as $pattern => $result ) {
			if ( stripos( $error_string, $pattern ) !== false ) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Pre-defined diagnostic rules.
	 */
	private static function get_rules() {
		return array(
			'Allowed memory size exhausted' => array(
				'diagnosis' => 'کمبود حافظه PHP',
				'cause'     => 'میزان حافظه تخصیص داده شده به PHP کمتر از نیاز پردازش‌های فعلی است.',
				'solution'  => 'افزایش مقدار memory_limit در فایل php.ini یا wp-config.php.',
			),
			'Call to undefined function wc_' => array(
				'diagnosis' => 'خطا در بارگذاری ووکامرس',
				'cause'     => 'تلاش برای فراخوانی توابع ووکامرس قبل از بارگذاری کامل آن یا غیرفعال بودن افزونه.',
				'solution'  => 'اطمینان از فعال بودن ووکامرس و استفاده از هوک‌های مناسب مانند plugins_loaded.',
			),
			'Headers already sent' => array(
				'diagnosis' => 'ارسال زودرس هدرها (Headers already sent)',
				'cause'     => 'وجود کاراکتر خالی یا خروجی قبل از شروع تگ php یا فراخوانی تابع header().',
				'solution'  => 'بررسی فایل‌های گزارش شده برای فضای خالی یا خروجی غیرمنتظره.',
			),
			'max_execution_time' => array(
				'diagnosis' => 'اتمام زمان اجرای اسکریپت',
				'cause'     => 'پردازش بیش از حد طولانی شده و از حد مجاز سرور فراتر رفته است.',
				'solution'  => 'افزایش max_execution_time در تنظیمات PHP.',
			),
			'Table doesn\'t exist' => array(
				'diagnosis' => 'خطای دیتابیس (جدول یافت نشد)',
				'cause'     => 'یکی از جداول دیتابیس وردپرس یا افزونه‌ها حذف شده یا نام آن تغییر کرده است.',
				'solution'  => 'بررسی صحت نصب افزونه مربوطه یا تعمیر دیتابیس.',
			),
		);
	}
}
