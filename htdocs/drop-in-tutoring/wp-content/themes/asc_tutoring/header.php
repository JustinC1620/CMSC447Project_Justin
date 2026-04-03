<?php
/**
 * Header template
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#ffffff">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <title><?php wp_title('&#8211;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/favicon-16x16.png">

    <link rel="stylesheet" href="<?php echo esc_url(get_stylesheet_uri()); ?>">

    <?php wp_head(); ?>
</head>

<body <?php body_class('theme-black banner-style-dark no-sidebar'); ?>>
<?php wp_body_open(); ?>

<div id="skipnav"><a href="#main">Skip to main content</a></div>

<div class="header-wrapper" data-silktide="ignore">
    <div class="container">
        <a class="umbc-logo-wrapper" href="https://www.umbc.edu" aria-label="UMBC" itemscope itemtype="https://schema.org/CollegeOrUniversity">
            <meta itemprop="name" content="UMBC" />

			<div class="umbc-logo">
			  <div style="position:absolute; width:0; height:0; overflow:hidden;" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg">
				  <g id="logo-umbc-white">
					<path d="M200,20.36C162.52,1,113.58,0,104.15,0h-3.1C91.62,0,42.67,1,5.17,20.36L0,23V132c.06.82,1.65,20.38,14.94,42.82,12.13,20.48,36.76,47.57,85.2,60.28l2.43.64,2.43-.64c48.43-12.71,73.07-39.8,85.2-60.28,13.29-22.44,14.88-42,14.94-42.82V23Zm-4.39,110.91s-5.34,71.55-93,94.57c-87.7-23-93-94.57-93-94.57V28.86C45.11,10.48,92,9.56,101.05,9.56h3.1c9.06,0,56,.92,91.49,19.3Z" fill="#fff"/><path d="M102.6,9.57h-1.55c-9,0-55.94.92-91.49,19.3v102.4s5.34,71.55,93,94.57c87.7-23,93-94.57,93-94.57V28.86c-35.55-18.38-82.43-19.3-91.49-19.3Zm82.92,107.17V129.1s-4.76,63.77-82.92,84.29a138.23,138.23,0,0,1-28.77-10.94,107.64,107.64,0,0,1-28.78-21.39c-23.33-24.63-25.37-52-25.37-52V37.82h0a131.83,131.83,0,0,1,25.37-9.7,204.46,204.46,0,0,1,28.78-5.65,217.94,217.94,0,0,1,28.77-1.84s47.7-1,82.92,17.19Z"/><path d="M73.83,89.36V22.47a204.46,204.46,0,0,0-28.78,5.65V62Z"/><path d="M45.05,28.12a131.83,131.83,0,0,0-25.37,9.7L45.05,62Z" fill="#fcb415"/><polygon points="45.05 61.97 45.05 117.7 73.83 145.08 73.83 89.36 45.05 61.97" fill="#fcb415"/><polygon points="45.05 61.97 19.68 37.82 19.68 37.82 19.68 93.54 45.05 117.7 45.05 61.97"/><path d="M102.6,116.74V20.63a217.94,217.94,0,0,0-28.77,1.84V89.36Z" fill="#fcb415"/><polygon points="73.83 145.08 102.6 172.46 102.6 116.74 73.83 89.36 73.83 145.08"/><path d="M173.5,128.75h-8.83v6.55c0,6.29-4.6,11.9-10.85,12.49a12,12,0,0,1-13.15-10.72c0-.43-.06-.85-.06-1.28v-7.07h-26v31.81h6.55c6.28,0,11.88,4.59,12.47,10.84a12,12,0,0,1-10.72,13.15c-.43,0-.85.06-1.27.06h-7.06v8.83a12,12,0,0,1-12,12v7.93c78.16-20.53,82.92-84.3,82.92-84.3v-12.3a12,12,0,0,1-12,12Z" fill="#fff"/><path d="M102.6,172.46,73.83,145.08v57.37a138.23,138.23,0,0,0,28.77,10.94Z" fill="#fcb415"/><path d="M102.6,20.63V28a12,12,0,0,1,12,12v8.82h7.06a12,12,0,0,1,12.06,11.94,10.69,10.69,0,0,1-.07,1.27c-.59,6.25-6.19,10.84-12.47,10.84h-6.55v31.8h26v-7a12,12,0,0,1,11.94-12.06c.43,0,.85,0,1.27.06,6.25.59,10.85,6.2,10.85,12.48v6.55h8.83a12,12,0,0,1,12,12V37.82C150.3,19.62,102.6,20.63,102.6,20.63Z" fill="#da2128"/><path d="M45.05,117.7v63.36a107.64,107.64,0,0,0,28.78,21.39V145.08Z"/><path d="M19.68,93.54V129.1s2,27.33,25.37,52V117.7Z" fill="#fcb415"/><path d="M173.5,104.71h-8.83V98.16c0-6.28-4.6-11.89-10.85-12.48a12,12,0,0,0-13.15,10.73c0,.42-.06.84-.06,1.27v7.06h-26V72.91h6.55c6.28,0,11.88-4.59,12.47-10.84a12,12,0,0,0-10.72-13.14,10.93,10.93,0,0,0-1.27-.07h-7.06V40a12,12,0,0,0-12-12v88.73h82.92a12,12,0,0,0-12-12Z" fill="#fff"/><path d="M102.6,172.46v33a12,12,0,0,0,12-12v-8.83h7.06a12,12,0,0,0,12.06-11.94,10.81,10.81,0,0,0-.07-1.27c-.59-6.25-6.19-10.84-12.47-10.84h-6.55V128.75h26v7.07a12,12,0,0,0,11.94,12.06,8.93,8.93,0,0,0,1.27-.06c6.25-.59,10.85-6.2,10.85-12.49v-6.55h8.83a12,12,0,0,0,12-12H102.6Z" fill="#da2128"/><path d="M352.46,230.75c-13.38,0-38.53-2.54-56.54-19.53-11.14-10.69-23.92-28.52-23.92-72V99.85c0-42,0-49.59-.54-58.17C271,33.39,269.35,29.39,261,27.62a47.19,47.19,0,0,0-8.87-.78c-1.74,0-3.59-1.07-3.59-3.05,0-3.59,4-3.59,5.75-3.59,8.37,0,18.94.39,25.25.62,3,.11,5,.19,5.77.19s3.31-.1,6.48-.22H292c6.32-.25,14.76-.58,20-.58,1.72,0,5.74,0,5.74,3.59,0,2-1.84,3.05-3.58,3.05a38.18,38.18,0,0,0-6.38.76c-6.14.92-8.37,4.47-8.92,14.08-.53,8.58-.53,16.15-.53,58.17v37.76c0,41.18,10,56.27,19.78,65.6,12.81,12,24.7,14.43,39.75,14.43,15.68,0,31.48-7.23,41.24-18.9,10.74-13.3,15.32-32.8,15.32-65.17V99.85c0-42,0-49.59-.54-58.17-.49-8.29-2.11-12.29-10.47-14.06a47,47,0,0,0-8.87-.78c-1.74,0-3.59-1.07-3.59-3.05,0-3.59,4-3.59,5.74-3.59,8.9,0,19.23.44,24.78.67,2.07.09,3.4.14,3.82.14.57,0,2.22-.08,4.34-.18H430c5-.24,12.5-.61,18-.61,1.72,0,5.75,0,5.75,3.59,0,2-1.85,3.05-3.59,3.05a22.27,22.27,0,0,0-6.4.76c-6.21,1.16-8.36,4.57-8.9,14.08-.54,8.58-.54,16.15-.54,58.17v28.86c0,52.54-13.52,71.27-25.3,81.72C388.48,228.64,367,230.75,352.46,230.75Z" fill="#fff"/><path d="M763.23,227.78c-3.86,0-16-.62-24.88-1.08l-1.09-.06c-5.12-.26-9.17-.48-9.91-.48-.37,0-2.08.07-4.44.17-3.82.15-70.24.27-75.47.27-1.72,0-5.75,0-5.75-3.59,0-1.51,1.11-3,3.59-3a38.43,38.43,0,0,0,7.78-1c.67-.13,3.87-1.18,2.74-9.1L643.17,74.4,576.78,215.28c-4.37,9.23-5.28,11.15-8.31,11.15-2.68,0-3.91-2.32-9.06-12.09l-.57-1.07c-2.93-5.53-10.63-20.58-30.69-60.85l-.88-1.76c-2.55-5.11-27.73-59.12-34.29-73.84L482.5,198.43c-.27,4.24-.27,9-.27,13.63a7.2,7.2,0,0,0,5.87,7,48.19,48.19,0,0,0,10,1.32c1.76,0,3.59,1,3.59,2.78,0,3.86-4.41,3.86-6.29,3.86-6.88,0-14.94-.38-20.28-.62h-.38c-2.2-.1-3.8-.18-4.43-.18-.82,0-2.91.1-5.58.23h-.44c-5.1.24-12,.57-16.63.57-4,0-6-1.3-6-3.86,0-1.83,2.07-2.78,4.13-2.78a33.14,33.14,0,0,0,7.81-.78c7.58-1.38,8.54-10.49,9.54-20.14l.11-1L482.66,22.57c.34-1.88,1.11-6.13,4.39-6.13,2.89,0,4.27,2.53,5.35,4.89l81.21,167,78-166.69c.85-1.91,2.29-5.14,5.34-5.14,3.47,0,4.18,4.06,4.66,8.57l18.07,165.61c1,9,2.66,23.87,13.59,27.38,5.35,1.72,11.66,1.64,13.41,1.12,4.58-1.38,5.66-6.66,6.6-13.43,1.33-11.22,1.33-32,1.33-58.35V99.85c0-42,0-49.59-.54-58.17-.49-8.29-2.11-12.29-10.47-14.06a47.19,47.19,0,0,0-8.87-.78,3.31,3.31,0,0,1-3.59-3.05c0-3.59,4-3.59,5.75-3.59,8.92,0,20.79.45,26.49.67l1.4.05,2.58.09h0c2.72,0,6.29-.15,10.06-.31,5.42-.22,12.13-.5,19.35-.5,43.71,0,55.37,26.33,55.37,41.89,0,22-12.63,35.84-24.18,47.91a76.87,76.87,0,0,1,29.24,18.59c10.63,11,16.25,24.49,16.25,39a57.82,57.82,0,0,1-17.54,41.22C803.12,221.22,784.91,227.78,763.23,227.78ZM739,118.56a3.38,3.38,0,0,1,0,.45v15.36c0,20.27,0,54.18.27,58.18l.06,1.07c.75,12.29.92,15.21,8.3,18.78,7.72,3.71,20.61,3.89,23.12,3.89,16.89,0,36.61-9.64,36.61-36.87,0-10.58-2.49-36.9-25.55-52.76-8.83-6-15.34-6.78-21.64-7.57q-3.62-.56-21-.54Zm14.75-88a60.06,60.06,0,0,0-12.61,1.31c-2.15.48-2.15.94-2.15,1.58v72.29c0,1.24.13,1.29,1,1.64a102.24,102.24,0,0,0,15.92,1c15.08,0,18.19-.49,23.48-6.53,6-6.8,9.36-16.39,9.36-26.31,0-20.72-9.18-44.94-35-44.94Z" fill="#fff"/><path d="M961,230.75c-39,0-67.58-9.37-90-29.49-22-19.71-33.12-46.93-33.12-80.91,0-21.28,8-52.07,30.63-74.65,19.73-19.7,47-29.28,83.3-29.28,6.5,0,23.34.32,39.37,3.26l.76.15c9.83,1.81,18.27,3.36,26.59,3.88,2.09.19,4.84.85,4.84,4.4a29.35,29.35,0,0,1-.31,3.5c-.29,2.34-.72,5.83-1,11.67-.31,5.09-.44,12.23-.55,18-.08,4.43-.14,8.08-.26,9.83-.18,2.79-.46,7-3.85,7-3.59,0-3.59-3.93-3.59-7.09,0-10.33-4.35-21-10.82-26.43-8.74-7.47-28.51-15-53-15-41.19,0-56.12,14.51-61,19.28C868,69,865.13,95.27,865.13,118.27A99.81,99.81,0,0,0,891.79,186c17.91,19.08,42.52,29.58,69.29,29.58,29.14,0,39.28-6.26,45.9-13,5.45-5.71,8.82-17.22,9.6-21.9.4-2.22.95-5.24,4.1-5.24,1,0,3.32.53,3.32,5.47,0,2.06-3.25,25.63-6,34.93-1.74,5.49-2.54,6.38-7.81,8.72C998.41,229.09,977.07,230.75,961,230.75Z" fill="#fff"/>
				  </g>
				  <g id="logo-umbc-gold">
					<path d="M200.05,20.36C162.54,1,113.59,0,104.16,0h-3.1C91.63,0,42.68,1,5.17,20.36L0,23V132c.06.82,1.65,20.38,14.94,42.82,12.13,20.48,36.77,47.58,85.21,60.29l2.43.64,2.43-.64c48.44-12.71,73.08-39.81,85.21-60.29,13.29-22.44,14.88-42,14.94-42.82V23Zm-4.39,110.93s-5.34,71.55-93,94.58c-87.71-23-93-94.58-93-94.58V28.86C45.12,10.48,92,9.56,101.06,9.56h3.1c9.06,0,56,.92,91.5,19.3Z" fill="#fcb415"/><path d="M102.61,9.57h-1.55c-9,0-55.94.92-91.5,19.3V131.29s5.34,71.55,93.05,94.58c87.71-23,93.05-94.58,93.05-94.58V28.86c-35.55-18.38-82.44-19.3-91.5-19.3Zm82.93,107.18v12.37s-4.76,63.77-82.93,84.3a138.26,138.26,0,0,1-28.77-10.95,107.64,107.64,0,0,1-28.78-21.39c-23.34-24.63-25.38-52-25.38-52V37.82h0a133.25,133.25,0,0,1,25.38-9.7,204.18,204.18,0,0,1,28.78-5.65,219.66,219.66,0,0,1,28.77-1.84s47.71-1,82.93,17.19Z"/><path d="M73.84,89.37V22.47a204.18,204.18,0,0,0-28.78,5.65V62Z"/><path d="M45.06,28.12a133.25,133.25,0,0,0-25.38,9.7L45.06,62Z" fill="#fcb415"/><polygon points="45.06 61.98 45.06 117.71 73.84 145.1 73.84 89.37 45.06 61.98" fill="#fcb415"/><polygon points="45.06 61.98 19.68 37.82 19.68 37.82 19.68 93.55 45.06 117.71 45.06 61.98"/><path d="M102.61,116.75V20.63a219.66,219.66,0,0,0-28.77,1.84v66.9Z" fill="#fcb415"/><polygon points="73.84 145.1 102.61 172.48 102.61 116.75 73.84 89.37 73.84 145.1"/><path d="M173.52,128.77h-8.83v6.55c0,6.29-4.6,11.9-10.85,12.49a12,12,0,0,1-13.15-10.73c0-.42-.06-.85-.06-1.27v-7.07h-26v31.81h6.55c6.28,0,11.89,4.59,12.48,10.84a12,12,0,0,1-10.73,13.15c-.42,0-.85.06-1.27.06H114.6v8.83a12,12,0,0,1-12,12v7.93c78.17-20.53,82.93-84.3,82.93-84.3V116.75a12,12,0,0,1-12,12Z" fill="#fff"/><path d="M102.61,172.48,73.84,145.1v57.37a138.26,138.26,0,0,0,28.77,10.95Z" fill="#fcb415"/><path d="M102.61,20.63V28a12,12,0,0,1,12,12v8.83h7.06a12,12,0,0,1,12.06,11.94c0,.42,0,.85-.06,1.27-.59,6.25-6.2,10.84-12.48,10.84h-6.55v31.8h26v-7A12,12,0,0,1,152.58,85.6c.42,0,.85,0,1.27.06,6.25.59,10.85,6.2,10.85,12.48v6.55h8.83a12,12,0,0,1,12,12V37.82C150.32,19.62,102.61,20.63,102.61,20.63Z" fill="#da2128"/><path d="M45.06,117.71v63.37a107.64,107.64,0,0,0,28.78,21.39V145.1Z"/><path d="M19.68,93.55v35.57s2,27.33,25.38,52V117.71Z" fill="#fcb415"/><path d="M173.52,104.72h-8.83V98.17c0-6.28-4.6-11.89-10.85-12.48a12,12,0,0,0-13.15,10.73c0,.42-.06.85-.06,1.27v7.06h-26V72.92h6.55c6.28,0,11.89-4.59,12.48-10.84a12,12,0,0,0-10.73-13.15c-.42,0-.85-.06-1.27-.06H114.6V40a12,12,0,0,0-12-12v88.74h82.93a12,12,0,0,0-12-12Z" fill="#fff"/><path d="M102.61,172.48v33a12,12,0,0,0,12-12v-8.83h7.06a12,12,0,0,0,12.06-11.94c0-.42,0-.85-.06-1.27-.59-6.25-6.2-10.84-12.48-10.84h-6.55V128.77h26v7.07a12,12,0,0,0,11.94,12.06c.42,0,.85,0,1.27-.06,6.25-.59,10.85-6.2,10.85-12.49V128.8h8.83a12,12,0,0,0,12-12H102.61Z" fill="#da2128"/><path d="M352.5,230.78c-13.38,0-38.53-2.54-56.55-19.54-11.14-10.69-23.92-28.52-23.92-72V99.86c0-42,0-49.59-.54-58.18C271,33.39,269.38,29.39,261,27.62a48.91,48.91,0,0,0-8.87-.78c-1.74,0-3.59-1.07-3.59-3.05,0-3.59,4-3.59,5.75-3.59,8.37,0,18.94.39,25.25.62,3,.11,5,.19,5.77.19s3.31-.1,6.48-.22H292c6.32-.25,14.77-.58,20-.58,1.72,0,5.74,0,5.74,3.59,0,2-1.84,3.05-3.58,3.05a38.06,38.06,0,0,0-6.38.76c-6.14.92-8.37,4.47-8.92,14.08-.54,8.59-.54,16.16-.54,58.18v37.77c0,41.18,10,56.27,19.79,65.6,12.81,12,24.7,14.44,39.75,14.44,15.68,0,31.49-7.24,41.25-18.91,10.74-13.3,15.32-32.8,15.32-65.17V99.86c0-42,0-49.59-.54-58.18-.49-8.29-2.11-12.29-10.47-14.06a48.91,48.91,0,0,0-8.87-.78c-1.74,0-3.59-1.07-3.59-3.05,0-3.59,4-3.59,5.74-3.59,8.9,0,19.23.44,24.78.67,2.07.09,3.4.14,3.82.14.57,0,2.22-.08,4.34-.18H430c5-.24,12.5-.61,18-.61,1.72,0,5.75,0,5.75,3.59,0,2-1.85,3.05-3.59,3.05a22.31,22.31,0,0,0-6.4.76c-6.21,1.16-8.36,4.57-8.9,14.08-.54,8.59-.54,16.16-.54,58.18v28.87c0,52.54-13.52,71.27-25.3,81.72C388.53,228.67,367,230.78,352.5,230.78Z" fill="#fcb415"/><path d="M763.32,227.81c-3.86,0-16-.62-24.88-1.08l-1.09-.06c-5.12-.26-9.17-.48-9.91-.48-.37,0-2.08.07-4.45.17-3.82.15-70.24.27-75.47.27-1.72,0-5.75,0-5.75-3.59,0-1.51,1.11-3,3.59-3a39.25,39.25,0,0,0,7.78-1c.67-.13,3.87-1.18,2.74-9.11L643.25,74.41l-66.4,140.9c-4.37,9.23-5.28,11.15-8.31,11.15-2.68,0-3.91-2.32-9.06-12.09l-.57-1.07c-2.93-5.54-10.64-20.59-30.7-60.86l-.88-1.76c-2.55-5.11-27.73-59.13-34.29-73.85L482.56,198.45c-.27,4.24-.27,9-.27,13.63a7.22,7.22,0,0,0,5.87,7,48.87,48.87,0,0,0,10,1.32c1.76,0,3.59,1,3.59,2.78,0,3.86-4.41,3.86-6.29,3.86-6.88,0-14.94-.38-20.28-.62h-.38c-2.2-.1-3.8-.18-4.43-.18-.82,0-2.92.1-5.59.23h-.44c-5.1.24-12,.57-16.63.57-4,0-6-1.3-6-3.86,0-1.83,2.07-2.78,4.13-2.78a33.08,33.08,0,0,0,7.81-.78c7.58-1.38,8.54-10.5,9.54-20.15l.11-1,19.42-175.9c.34-1.88,1.11-6.13,4.39-6.13,2.89,0,4.27,2.53,5.35,4.89l81.22,167,78-166.71c.85-1.91,2.29-5.14,5.34-5.14,3.47,0,4.18,4.06,4.66,8.57l18.07,165.63c1,9,2.66,23.88,13.59,27.39,5.35,1.72,11.66,1.64,13.41,1.12,4.58-1.38,5.66-6.67,6.6-13.44,1.33-11.22,1.33-32,1.33-58.35V99.86c0-42,0-49.59-.54-58.18-.49-8.29-2.11-12.29-10.47-14.06a48.91,48.91,0,0,0-8.87-.78,3.31,3.31,0,0,1-3.59-3.05c0-3.59,4-3.59,5.75-3.59,8.92,0,20.79.45,26.49.67l1.4.05,2.59.09h0c2.72,0,6.29-.15,10.06-.31,5.42-.22,12.13-.5,19.35-.5,43.71,0,55.38,26.34,55.38,41.9,0,22-12.64,35.84-24.19,47.91a76.93,76.93,0,0,1,29.25,18.6c10.63,11,16.25,24.49,16.25,39A57.82,57.82,0,0,1,816,208.83C803.21,221.25,785,227.81,763.32,227.81ZM739.13,118.57a3.38,3.38,0,0,1,0,.45v15.37c0,20.27,0,54.18.27,58.18l.06,1.07c.75,12.29.92,15.21,8.3,18.78,7.72,3.72,20.61,3.9,23.12,3.9,16.89,0,36.61-9.65,36.61-36.88,0-10.58-2.49-36.9-25.55-52.77-8.83-6-15.34-6.78-21.64-7.57q-3.62-.55-21-.54Zm14.75-88a60.06,60.06,0,0,0-12.61,1.31c-2.15.48-2.15.94-2.15,1.58v72.3c0,1.24.13,1.29,1,1.64a102.16,102.16,0,0,0,15.92,1c15.08,0,18.19-.49,23.48-6.53,6-6.8,9.36-16.39,9.36-26.31,0-20.72-9.18-44.95-35-44.95Z" fill="#fcb415"/><path d="M961.07,230.78c-39,0-67.59-9.37-90-29.5-22-19.71-33.12-46.93-33.12-80.92,0-21.28,8-52.07,30.63-74.65,19.73-19.71,47-29.29,83.31-29.29,6.5,0,23.34.32,39.38,3.26l.76.15c9.83,1.81,18.27,3.36,26.59,3.88,2.09.19,4.84.85,4.84,4.4a29.35,29.35,0,0,1-.31,3.5c-.29,2.34-.72,5.83-1,11.68-.31,5.09-.44,12.23-.55,18-.08,4.43-.14,8.08-.26,9.83-.18,2.79-.46,7-3.85,7-3.59,0-3.59-3.93-3.59-7.09,0-10.33-4.35-21-10.82-26.43-8.74-7.48-28.52-15-53-15-41.19,0-56.13,14.52-61,19.29-21,20.15-23.85,46.39-23.85,69.39A99.82,99.82,0,0,0,891.89,186c17.92,19.08,42.53,29.59,69.3,29.59,29.15,0,39.29-6.27,45.91-13,5.45-5.71,8.82-17.22,9.6-21.9.4-2.22.95-5.24,4.1-5.24,1,0,3.32.53,3.32,5.47,0,2.06-3.25,25.63-6,34.94-1.74,5.49-2.54,6.38-7.81,8.72C998.53,229.12,977.18,230.78,961.07,230.78Z" fill="#fcb415"/>
				  </g>
				</svg>
			  </div>

			  <svg viewBox="0 0 1024 235.75" class="logo-gold" role="img" aria-label="UMBC">
				<use href="#logo-umbc-gold" xlink:href="#logo-umbc-gold"></use>
			  </svg>

			  <svg viewBox="0 0 1024 235.75" class="logo-white" role="img" aria-label="UMBC">
				<use href="#logo-umbc-white" xlink:href="#logo-umbc-white"></use>
			  </svg>
			</div>
        </a>

        <div class="mobile-header-wrapper">
            <p class="page-header-title mobile-header-title">
                <a href="<?php echo esc_url(home_url('/')); ?>" tabindex="-1">Academic Success Center</a>
            </p>
            <button class="menu-toggle button" aria-controls="primary-menu" aria-expanded="false" data-target="#primary-menu" type="button">
                <span class="menu-toggle-content">Menu</span>
                <span class="lines"></span>
            </button>
        </div>

        <div class="utility-navigation-wrapper">
            <ul class="list-unstyled offsite-links-list">
                <li class="button-apply-list">
                    <a class="button button-primary button-apply" href="https://umbc.edu/apply/">
                        Apply
                    </a>
                </li>
                <li>
                    <a href="https://my.umbc.edu" class="utility-nav-myUMBC" aria-label="myUMBC">myUMBC</a>
                </li>
                <li>
                    <a href="https://www2.umbc.edu/search/directory/" class="utility-nav-directory" aria-label="directory">
                        Directory
                    </a>
                </li>
                <li>
                    <button type="submit" class="button button-search-icon" aria-label="search-icon">
                        <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.0398 10.1074H11.3215L11.0479 9.83372C11.9486 8.8418 12.4845 7.49644 12.4845 6.05986C12.4845 2.82186 9.87354 0.210938 6.63554 0.210938C3.39754 0.210938 0.786621 2.82186 0.786621 6.05986C0.786621 9.29786 3.39754 11.9088 6.63554 11.9088C8.07212 11.9088 9.41748 11.3729 10.4094 10.4722L10.683 10.7458V11.4641L15.1752 15.9563L16.5206 14.6109L12.0398 10.1074ZM6.64694 10.1074C4.40086 10.1074 2.59944 8.30594 2.59944 6.05986C2.59944 3.81378 4.40086 2.01236 6.64694 2.01236C8.89302 2.01236 10.6944 3.81378 10.6944 6.05986C10.683 8.31734 8.89302 10.1074 6.64694 10.1074Z" fill="currentColor"/>
                        </svg>
                    </button>

                    <div id="search-popup" class="search-popup">
                        <div class="search-popup-content">
                            <button class="search-close" aria-label="Close">×</button>

                            <div class="search-content">
                                <h2 class="search-heading">Search UMBC</h2>
                                <form class="search-form search-form-desktop" action="https://www.umbc.edu/stats/searchthru/">
                                    <fieldset>
                                        <legend class="sr-only">Search Context</legend>
                                        <label>
                                            <input class="search-this-site" id="this-site-desktop" type="radio" name="search_context" value="site:https://academicsuccess.umbc.edu/" checked>
                                            <span>This Site</span>
                                        </label>
                                        <label>
                                            <input class="search-umbc" id="umbc.edu-desktop" type="radio" name="search_context" value="">
                                            All of UMBC
                                        </label>
                                    </fieldset>
                                    <div class="form-row">
                                        <label>
                                            <span class="label-text sr-only">Search</span>
                                            <input class="q_temp" type="text" name="q_temp">
                                            <input class="q" type="hidden" name="q">
                                        </label>
                                        <button type="submit" class="button button-primary button-search" aria-label="search">Search</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>

            <form class="search-form search-form-mobile" action="https://www.umbc.edu/stats/searchthru/">
                <div class="form-row">
                    <label>
                        <span class="label-text sr-only">Search</span>
                        <input class="q_temp" type="text" name="q_temp" placeholder="Search">
                        <input class="q" type="hidden" name="q">
                    </label>
                    <button type="submit" class="button button-search-mobile" aria-label="search">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" role="img" aria-label="search">
                            <path fill="currentColor" d="m761.2 682.8c25.2 25 50.1 49.8 74.9 74.6 55.8 55.7 111.5 111.4 167.3 167.2 28.3 28.3 27.3 67.1-2.1 88.6-23.5 17.1-52.6 13.8-75.3-8.7-23.1-22.9-46.1-46.1-69.1-69.1-55.7-55.8-111.5-111.5-167.2-167.2-2.1-2.1-4.3-4-6.9-6.5-109.8 79.8-231 106.5-362.9 73.7-104.4-25.9-186.8-85.5-247.3-174.2-120-175.8-88.2-415.3 72.9-556.2 161.7-141.4 401.8-139.9 562.4 4 159.1 142.5 192.3 388.6 53.3 573.8zm-23.3-258.4c.1-172.6-140.5-313.1-313.4-313.2s-313.2 140.2-313.2 313.2c0 172.8 140.6 313.5 313.2 313.4 172.7-.1 313.3-140.8 313.4-313.4z"></path>
                        </svg>
                    </button>
                </div>
                <fieldset>
                    <legend class="sr-only">Search Context</legend>
                    <label>
                        <input class="search-this-site" id="this-site-mobile" type="radio" name="search_context" value="site:https://sites.umbc.edu/" checked>
                        <span>This Site</span>
                    </label>
                    <label>
                        <input class="search-umbc" id="umbc.edu-mobile" type="radio" name="search_context" value="">
                        All of UMBC
                    </label>
                </fieldset>
            </form>
        </div>
    </div>
</div>

<header class="page-header-wrapper">
    <div class="page-header-background background-pattern" data-silktide="ignore">
        <svg class="flag-pattern" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <pattern patternTransform="rotate(-6) scale(250)" id="pattern" x="0" y="0" width="1" height="0.667" patternUnits="userSpaceOnUse" fill="currentColor">
                    <path d="M.416.333V.222L.333.166v.112zM.084 0l.083.055V0zM.333.166V0H.25v.11L.167.056v.112L.25.223V.11zM0 0v.056l.084.056V0zM.167.333V.167L.084.112v.221zM.833.612L.75.556v.111h.083V.612l.083.055V.555L.833.5zM.916.555L1 .611V.333H.916z"/>
                    <path d="M.25.333v.023a.021.021 0 01.021.021V.39h.02a.021.021 0 010 .042h-.02v.048h.134V.46a.021.021 0 11.042 0v.02h.018A.021.021 0 01.485.5H.25v.144A.021.021 0 00.271.623V.612h.02a.021.021 0 100-.043h-.02V.521h.134v.02a.021.021 0 10.042 0v-.02h.018a.021.021 0 00.02-.02H.5V.388l.084.056V.333L.667.39V.333H.75v.111L.667.39V.5L.75.556V.444L.833.5V.333H.75V.31A.021.021 0 01.729.29V.277h-.02A.021.021 0 01.708.236h.02V.188H.596v.02a.021.021 0 11-.042 0v-.02H.535a.021.021 0 01-.02-.021H.5V0H.416v.222L.5.278v.055H.333V.278L.25.223z"/>
                    <path d="M.667.667V.501L.584.445v.222zM.75 0v.023a.021.021 0 01.021.02v.012h.02a.021.021 0 010 .043h-.02v.048h.134v-.02a.021.021 0 11.042 0v.02h.018a.021.021 0 01.02.02H1V0zM.729.044v.011h-.02a.021.021 0 000 .043h.02v.048H.595v-.02A.021.021 0 10.553.125v.02H.535a.021.021 0 00-.02.022H.75V.023a.021.021 0 00-.021.02zM.926.229A.021.021 0 00.947.208v-.02h.018a.021.021 0 00.02-.021H.75V.31A.021.021 0 00.771.29V.277h.02a.021.021 0 100-.042h-.02V.188h.134v.02a.021.021 0 00.021.02zM.229.623V.612h-.02a.021.021 0 110-.043h.02V.521H.095v.02a.021.021 0 11-.042 0v-.02H.035a.021.021 0 01-.02-.02H0v.166h.25V.644A.021.021 0 01.229.623zM.229.377V.39h-.02a.021.021 0 000 .042h.02v.048H.095V.46a.021.021 0 10-.042 0v.02H.035A.021.021 0 00.015.5H.25V.356a.021.021 0 00-.021.021z"/>
                </pattern>
            </defs>
            <rect fill="url(#pattern)" width="100%" height="100%"/>
        </svg>
    </div>

    <div class="page-header-content" data-silktide="ignore">
        <div class="container">
            <p class="page-header-sub-title">
                <a href="https://uaa.umbc.edu/">Division of Undergraduate Academic Affairs</a>
            </p>

            <p class="h1 page-header-title">
                <a href="<?php echo esc_url(home_url('/')); ?>">Academic Success Center</a>
            </p>
        </div>
    </div>

    <div class="navigation-wrapper">
        <div class="navigation-menu-wrapper">
            <div class="container">
                <nav id="site-navigation" class="main-navigation">
                    <div class="menu-container">
                        <ul id="primary-menu" class="menu">
                            <li class="menu-item menu-item-has-children top-level">
                                <a href="https://academicsuccess.umbc.edu/">Academic Success Center</a>
                                <ul class="sub-menu">
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/staff/">Academic Success Center Staff</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/message-from-associate-vice-provost-and-dean/">Message from Associate Vice Provost and Assistant Dean</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/mission-and-core-values/">Mission and Core Values</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/innovation-recognition-national-reach/">Innovation, Recognition and National Reach</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/claim-your-future/academic-success-center-advisory-board/">Academic Success Center Advisory Board</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/faculty-and-staff-resources/">Faculty and Staff Resources</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/partner-with-the-asc/">Partner with the ASC</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/work-with-the-academic-success-center/">Employment Opportunities with the ASC</a></li>
                                    <li class="menu-item"><a href="https://www.givecampus.com/campaigns/41278/donations/new?designation=andreavsprattlearningresourcecenter&">Giving</a></li>
                                </ul>
                            </li>
                            <li class="menu-item menu-item-has-children top-level">
                                <a href="https://academicsuccess.umbc.edu/academic-policy/">Academic Policy</a>
                                <ul class="sub-menu">
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/course-repeat-petitions/">Course Repeat Petitions</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/late-add-petitions/">Late-Add Petitions</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/graduation-requirement-petition/">Graduation Requirement Petitions</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/grading-method-petition/">Grading Method Petition</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/non-applicable-semester-petition/">Non-Applicable Semester Petitions</a></li>
                                </ul>
                            </li>
                            <li class="menu-item menu-item-has-children top-level">
                                <a href="https://academicsuccess.umbc.edu/academic-learning-resources/">Academic Learning Resources</a>
                                <ul class="sub-menu">
                                    <li class="menu-item menu-item-has-children">
                                        <a href="https://academicsuccess.umbc.edu/tutoring/">Tutoring</a>
                                        <ul class="sub-menu">
                                            <li class="menu-item"><a href="https://academicsuccess.umbc.edu/appointment-tutoring/">Appointment Tutoring</a></li>
                                            <li class="menu-item"><a href="https://academicsuccess.umbc.edu/drop-in-tutoring/">Drop-In Tutoring</a></li>
                                            <li class="menu-item"><a href="https://academicsuccess.umbc.edu/writing-center/">Writing Center</a></li>
                                        </ul>
                                    </li>
                                    <li class="menu-item menu-item-has-children">
                                        <a href="https://academicsuccess.umbc.edu/si-pass/">SI PASS</a>
                                        <ul class="sub-menu">
                                            <li class="menu-item"><a href="https://academicsuccess.umbc.edu/si-pass/si-pass-schedule/">SI PASS Schedule</a></li>
                                        </ul>
                                    </li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/student-resources/">Student Resources</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/academic-success-meetings/">Academic Skills Meetings</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/early-academic-alerts/">Early Academic Alerts</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/placement-testing/">Placement Testing</a></li>
                                </ul>
                            </li>
                            <li class="menu-item menu-item-has-children top-level">
                                <a href="https://academicsuccess.umbc.edu/academic-advocacy/">Academic Advocacy</a>
                                <ul class="sub-menu">
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/coordinated-care/">Coordinated Care</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/academic-peer-advocate-program/">Academic Peer Advocacy</a></li>
                                    <li class="menu-item"><a href="https://academicsuccess.umbc.edu/submit-a-referral/">Submit an Academic Advocate Referral</a></li>
                                </ul>
                            </li>
                        </ul>

                        <ul class="list-unstyled offsite-links-list">
                            <li class="button-apply-list">
                                <a class="button button-primary button-apply" href="https://umbc.edu/apply/">Apply</a>
                            </li>
                            <li>
                                <a href="https://my.umbc.edu" class="utility-nav-myUMBC" aria-label="myUMBC">myUMBC</a>
                            </li>
                            <li>
                                <a href="https://www2.umbc.edu/search/directory/" class="utility-nav-directory" aria-label="directory">Directory</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>