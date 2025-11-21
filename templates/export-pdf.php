<?php
/**
 * PDF export template for report logs.
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
<html>
<head>
        <meta charset="utf-8">
        <title><?php echo esc_html__( 'Satori Report Log Export', 'satori-report-logs' ); ?></title>
        <style>
                body {
                        font-family: DejaVu Sans, sans-serif;
                        background: #ffffff;
                        color: #111;
                        margin: 0;
                        padding: 20px;
                }
                .report-container {
                        border: 1px solid #dcdcdc;
                        border-radius: 6px;
                        padding: 20px;
                }
                .report-header {
                        border-bottom: 2px solid #2271b1;
                        padding-bottom: 10px;
                        margin-bottom: 18px;
                }
                .report-header h1 {
                        margin: 0;
                        font-size: 22px;
                }
                .report-header p {
                        margin: 4px 0 0;
                        font-size: 12px;
                }
                .report-meta {
                        display: table;
                        width: 100%;
                        border-spacing: 12px 0;
                        margin-bottom: 16px;
                }
                .meta-card {
                        display: table-cell;
                        background: #f4f7fb;
                        border: 1px solid #d0e4f7;
                        border-radius: 6px;
                        padding: 10px;
                        width: 33%;
                }
                .meta-card h3 {
                        margin: 0 0 6px;
                        font-size: 12px;
                        letter-spacing: 0.04em;
                        text-transform: uppercase;
                }
                .meta-card p {
                        margin: 0;
                        font-size: 12px;
                }
                .section {
                        margin-bottom: 18px;
                }
                .section h2 {
                        margin: 0 0 8px;
                        font-size: 14px;
                        border-left: 4px solid #2271b1;
                        padding-left: 8px;
                }
                .section .content {
                        border: 1px solid #e5e7eb;
                        border-radius: 6px;
                        padding: 10px;
                        font-size: 12px;
                        line-height: 1.5;
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
