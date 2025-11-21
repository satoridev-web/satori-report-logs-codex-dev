<?php
/**
 * HTML export template for report logs.
 *
 * @var array $report Report data.
 * @var array $items  Report items.
 *
 * @package Satori_Report_Logs
 */

$headers  = array();
$sections = array();

foreach ( $items as $item ) {
        $type = $item['row_type'] ?? '';

        if ( 'header' === $type ) {
                $headers[] = $item;
        } elseif ( 'section' === $type ) {
                $sections[] = $item;
        }
}

$month_name = isset( $report['month'] ) ? date_i18n( 'F', mktime( 0, 0, 0, (int) $report['month'], 10 ) ) : '';
$year       = isset( $report['year'] ) ? absint( $report['year'] ) : '';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo esc_html__( 'Satori Report Log Export', 'satori-report-logs' ); ?></title>
        <style>
                body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, 'Open Sans', sans-serif;
                        background: #f8f9fa;
                        color: #1d2327;
                        margin: 0;
                        padding: 20px;
                }
                .report-container {
                        background: #fff;
                        border: 1px solid #dcdcdc;
                        border-radius: 6px;
                        padding: 24px;
                        max-width: 900px;
                        margin: 0 auto;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                }
                .report-header {
                        border-bottom: 2px solid #2271b1;
                        padding-bottom: 12px;
                        margin-bottom: 20px;
                }
                .report-header h1 {
                        margin: 0 0 6px;
                        font-size: 26px;
                        color: #1d2327;
                }
                .report-meta {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 12px;
                        margin-bottom: 20px;
                }
                .meta-card {
                        background: #f0f6fc;
                        border: 1px solid #d0e4f7;
                        border-radius: 6px;
                        padding: 12px;
                }
                .meta-card h3 {
                        margin: 0 0 6px;
                        font-size: 14px;
                        letter-spacing: 0.04em;
                        text-transform: uppercase;
                        color: #1d2327;
                }
                .meta-card p {
                        margin: 0;
                        font-size: 15px;
                        color: #111;
                }
                .section {
                        margin-bottom: 22px;
                }
                .section h2 {
                        margin: 0 0 10px;
                        font-size: 18px;
                        color: #1d2327;
                        border-left: 4px solid #2271b1;
                        padding-left: 10px;
                }
                .section .content {
                        background: #fbfbfb;
                        border: 1px solid #e5e7eb;
                        border-radius: 6px;
                        padding: 14px;
                        line-height: 1.6;
                }
        </style>
</head>
<body>
<div class="report-container">
        <div class="report-header">
                <h1><?php echo esc_html__( 'Monthly Service Report', 'satori-report-logs' ); ?></h1>
                <p><?php echo esc_html( trim( $month_name . ' ' . $year ) ); ?></p>
        </div>

        <?php if ( ! empty( $headers ) ) : ?>
                <div class="report-meta">
                        <?php foreach ( $headers as $header ) : ?>
                                <div class="meta-card">
                                        <h3><?php echo esc_html( $header['label'] ?? '' ); ?></h3>
                                        <p><?php echo esc_html( $header['value'] ?? '' ); ?></p>
                                </div>
                        <?php endforeach; ?>
                </div>
        <?php endif; ?>

        <?php foreach ( $sections as $section ) : ?>
                <div class="section">
                        <h2><?php echo esc_html( $section['label'] ?? '' ); ?></h2>
                        <div class="content">
                                <?php echo wp_kses_post( wpautop( $section['value'] ?? '' ) ); ?>
                        </div>
                </div>
        <?php endforeach; ?>
</div>
</body>
</html>
