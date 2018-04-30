<?php
/*
Plugin Name: Domates Bot WP Plugin
Plugin URI: https://domates.com
Description: Domates bot sistemi için gerekli olan eklenti.
Version: 1.00
Author: DOMATES
Author URI: https://domates.com
License: GPLv2
*/

define('domates_NAME', 'Domates Bot WP Plugin');
define('domates_VERSION', '1.00');

function hashGetir() {
	$domates = plugin_dir_path(__FILE__) . "domates.php";
	$hash = hash_file('sha256', $domates);
	return $hash;
}

// echo hashGetir();
function dashboard_widget_function($post, $callback_args) {
	echo "Hello World, this is my first Dashboard Widgets!";
}

// Function used in the action hook
function add_dashboard_widgets() { //TODO: buraya bak sonrasında.
	wp_add_dashboard_widget('dashboard_widget', 'Example Dashboard Widget', 'dashboard_widget_function');
}

add_action('wp_dashboard_setup', 'add_dashboard_widgets');

add_action('admin_menu', 'eklentim_yonetim');
function eklentim_yonetim() {
	add_menu_page('İçerik Converter', 'İçerik Converter', '8', 'icerikConverter', 'yarbotConverterFonksiyon', 'dashicons-desktop');
}

function yarbotConverterFonksiyon() {
	?>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type="text/javascript">
        jQuery("body").on("click", "#tumunuDonustur", function () {
            jQuery("table#yarbotConverterTable tbody tr").each(function () {
                console.log(jQuery(this).find("td:nth-child(1)").html());
                //TODO: ajax isteği ile panele gönder. Çıktısını bekle.

            });
        });

	</script>
	<div class="container">
		<h2>YARBOT CONVERTER</h2>
		<p>Bu aracı kullanarak daha önceden yarbot ile eklediğiniz tüm içeriği DOMATES sistemine uyarlayabilirsiniz.</p>
		<div style="text-align: right;">
			<button id="tumunuDonustur" type="button" class="btn btn-success">TÜMÜNÜ DÖNÜŞTÜR</button>
		</div>
		<table id="yarbotConverterTable" class="table table-striped">
			<thead>
			<tr>
				<th>ID</th>
				<th>Video Başlığı</th>
				<th>Video Kaynağı</th>
				<th>Video ID</th>
				<th>Eklenme Tarihi</th>
				<th>İşlem</th>
			</tr>
			</thead>
			<tbody>
			<?php>
    $kacAdet = 0;
    $args = [
        'posts_per_page'   => 15000,
        'offset'           => 0,
        'orderby'          => 'post_date',
        'order'            => 'ASC',
        'post_type'        => 'post',
        'post_status'      => 'publish',
        ];
    $myposts = get_posts( $args );
    foreach ($myposts as $key => $post) {
        if (preg_match("/<iframe.*src=\"(.*)\".*><\/iframe>/isU", get_post_meta($post->ID,"dp_video_code",true),$eslesmeler)) {
            $ayrilmis = explode("/",$eslesmeler[1]);
            $videoIDAyrilmis = explode(".",$ayrilmis[5]);
            if($ayrilmis[3] == "player" && $videoIDAyrilmis[0] > 0){
                ?>
			<tr>
				<td><?php echo $post->ID; ?></td>
				<td>
					<a href="<?php echo get_post_permalink($post->ID); ?>" target="_blank"><?php echo $post->post_title; ?></a>
				</td>
				<td><?php echo $ayrilmis[4]; ?></td>
				<td><?php echo $videoIDAyrilmis[0]; ?></td>
				<td><?php echo $post->post_date_gmt; ?></td>
				<td>
					<button type="button" class="btn btn-primary">Dönüştür</button>
				</td>
			</tr>
			<?php
			$kacAdet++;
			}
			}
			}

			?>
			</tbody>
		</table>
		<?php echo $kacAdet . " adet çevrilebilecek video bulundu."; ?>
	</div>
	<?php
}


add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'my_plugin_action_links');
add_action('admin_init', 'nht_plugin_redirect');
function my_plugin_action_links($links) {
	$links[] = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=domates-baglanti-ayar')) . '">Ayarlar</a>';
	return $links;
}

function nht_plugin_redirect() {
	if(get_option('nht_plugin_do_activation_redirect', false)) {
		delete_option('nht_plugin_do_activation_redirect');
		if(!isset($_GET['activate-multi'])) {
			wp_redirect("options-general.php?page=domates-baglanti-ayar");
		}
	}
}

function eklentiFirstRun() {
	if(get_option('domatesEssizKey')) {
		//daha önceden aktif edilmiş.!!
		//TODO: bu key başka site tarafından kullanılıyor mu kontrol et.
		add_option('nht_plugin_do_activation_redirect', true);
	} else {
		add_option('domatesHost', "https://okancelik.com.tr/domates/");
		$domatesEssizKey = wp_hash_password(wp_generate_password(10, true, true));
		add_option("domatesEssizKey", $domatesEssizKey);
		add_option('nht_plugin_do_activation_redirect', true);
	}
}

register_activation_hook(__FILE__, 'eklentiFirstRun');

function domatesVideoIcerikFonksiyon($atts) {
	$videoID = $atts['videoid'];
	$imageURL = $atts['largephoto'];
	$URL = plugin_dir_url(__FILE__) . 'player/player.php?v=' . $videoID . '&i=' . $imageURL . '&h=' . base64_encode(get_option('domatesHost'));
	return '<iframe frameborder="0" src="' . $URL . '" class="embed-responsive-item" 
    scrolling="no"></iframe>';
}

add_shortcode('domatesVideoIcerik', 'domatesVideoIcerikFonksiyon');

add_action('rest_api_init', function () {
	register_rest_route('domates/v1', '/addPost', [
		'methods' => 'POST',
		'callback' => 'postEkle',
	]);
});
add_action('rest_api_init', function () {
	register_rest_route('domates/v1', '/getCategories', [
		'methods' => 'GET',
		'callback' => 'kategoriVer',
	]);
});
add_action('rest_api_init', function () {
	register_rest_route('domates/v1', '/ping', [
		'methods' => 'GET',
		'callback' => 'pingTest',
	]);
});
add_action('rest_api_init', function () {
	register_rest_route('domates/v1', '/getPosts', [
		'methods' => 'GET',
		'callback' => 'icerikVer',
	]);
});
add_action('rest_api_init', function () {
	register_rest_route('domates/v1', '/getSinglePost', [
		'methods' => 'GET',
		'callback' => 'tekliIcerikVer',
	]);
});
function postEkle($data) {
	//gerekli alanlar: postTitle, postContent, postStatus, postCategory, postType, duration, videoID, seoDesc, desc, resimURL, postTags, postDateGMT, postDate
	if($data['securityKey'] == get_option('domatesEssizKey')) {
		$get = wp_remote_get($data['resimURL']);
		$type = wp_remote_retrieve_header($get, 'content-type');
		$upload = wp_upload_bits(sanitize_title($data['postTitle']) . ".jpg", '', wp_remote_retrieve_body($get));
		$resimURL = $upload["url"];
		//TODO: detube olmayan için contente yazdır.
		$my_post = [
			'post_title' => wp_strip_all_tags($data['postTitle']),
			'post_content' => $data['postContent'],
			'post_status' => $data['postStatus'],
			'post_name' => sanitize_title($data['postTitle']),
			'post_author' => 1,
			'post_type' => $data['postType'],
			'post_category' => $data['postCategory'],
			'tags_input' => $data['postTags'],
			'post_date_gmt' => $data['postDateGMT'],
			'post_date' => $data['postDate'],
			'edit_date' => 'true',
			'meta_input' => [
				'domatesBotIcerik' => 'true',
				'duration' => $data['duration'],
				'dp_video_code' => '[domatesVideoIcerik videoid="' . $data['videoID'] . '" largephoto="' . base64_encode($resimURL) . '"]',
				'dp_video_poster' => $resimURL,
				'video_type' => 'embed',
				'_aioseop_description' => $data['seoDesc'],
				'_yoast_wpseo_metadesc' => $data['seoDesc'],
				'description' => $data['desc'],
			],
		];
		$postid = wp_insert_post($my_post);
		$attachment = [
			'post_title' => wp_strip_all_tags($data['postTitle']),
			'post_mime_type' => $upload["type"],
		];
		$attach_id = wp_insert_attachment($attachment, $upload['file'], $postid);
		set_post_thumbnail($postid, $attach_id);
		return true;
	} else {
		return false;
	}
}

function pingTest($data) {
	if($data['securityKey'] == get_option('domatesEssizKey')) {
		return true;
	} else {
		return false;
	}
}

function kategoriVer($data) {
	if($data['securityKey'] == get_option('domatesEssizKey')) {
		$categories = get_categories([
			'orderby' => 'name',
			'order' => 'ASC',
		]);
		return $categories;
	} else {
		return false;
	}
}

function icerikVer($data) {
	if($data['securityKey'] == get_option('domatesEssizKey') || 1 == 1) { //TODO: bunu kaldır.
		if(!isset($data['postsPerPage']) || !$data['postsPerPage']) {
			$data['postsPerPage'] = 20;
		}
		if(!isset($data['page']) || !$data['page']) {
			$data['page'] = 1;
		}
		if(!isset($data['order']) || !$data['order']) {
			$data['order'] = 'desc';
		}
		$args = [
			'posts_per_page' => $data['postsPerPage'],
			'offset' => ($data['page'] - 1) * $data['postsPerPage'],
			'orderby' => 'date',
			'order' => $data['order'],
			'meta_key' => 'domatesBotIcerik',
			'meta_value' => 'true',
			'post_type' => 'post',
			'suppress_filters' => true,
		];
		$posts_array = get_posts($args);
		return $posts_array;
	} else {
		return false;
	}
}

function tekliIcerikVer($data) {
	if($data['securityKey'] == get_option('domatesEssizKey') || 1 == 1) { //TODO: bunu kaldır.
		if(!isset($data['postsPerPage']) || !$data['postsPerPage']) {
			$data['postsPerPage'] = 20;
		}
		if(!isset($data['page']) || !$data['page']) {
			$data['page'] = 1;
		}
		if(!isset($data['order']) || !$data['order']) {
			$data['order'] = 'desc';
		}
		$args = [
			'posts_per_page' => $data['postsPerPage'],
			'offset' => ($data['page'] - 1) * $data['postsPerPage'],
			'orderby' => 'date',
			'order' => $data['order'],
			'meta_key' => 'domatesBotIcerik',
			'meta_value' => 'true',
			'post_type' => 'post',
			'suppress_filters' => true,
		];
		$posts_array = get_posts($args);
		return $posts_array;
	} else {
		return false;
	}
}

class MySettingsPage {

	private $options;

	public function __construct() {
		add_action('admin_menu', [$this, 'add_plugin_page']);
		add_action('admin_init', [$this, 'page_init']);
	}

	public function add_plugin_page() {
		add_options_page(
			'Settings Admin',
			'Domates Bot Ayarlar',
			'manage_options',
			'domates-baglanti-ayar',
			[$this, 'create_admin_page']
		);
	}

	public function create_admin_page() {
		// Set class property
		$this->options = get_option('my_option_name');
		?>
		<div class="wrap">
			<h1>Domates Bot Ayarlar</h1>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields('my_option_group');
				do_settings_sections('domates-baglanti-ayar');
				submit_button();
				?>
			</form>
			<?php
			if(isset($this->options['id_number'])) {
				if(get_option('domatesPanelBaglantisi')) {
					echo "reconnect";
					// update_option("domatesPanelBaglantisi", false);
				} else {
					echo "bağlanıyor....";
					$url = get_option('domatesHost') . "wp-first-run";
					$response = wp_remote_post($url, [
							'method' => 'POST',
							'timeout' => 45,
							// 'redirection' => 5,
							// 'httpversion' => '1.0',
							'blocking' => true,
							'headers' => [],
							'body' => ['siteURL' => get_site_url(), 'publicKey' => $this->options['id_number'], 'secretKey' => get_option('domatesEssizKey')],
							'cookies' => [],
						]
					);
					if(is_wp_error($response)) {
						$error_message = $response->get_error_message();
						echo "<br />Hata oluştu: $error_message";
					} else {
						$response['body'] = json_decode($response['body']);
						echo '<br />Yanıt:';
						// print_r( $response['body'] );
						if($response['body']->success == true) {
							update_option("domatesPanelBaglantisi", true);
							echo '<br /> BAŞARILI: Eklentiniz başarıyla panelinize bağlandı!';
						} else {
							update_option("domatesPanelBaglantisi", false);
							echo '<br /> HATA: ' . $response['body']->hata;
							echo '<br /> Lütfen değerleri kontrol ederek tekrar deneyiniz.';
						}
					}
				}
			}
			?>
		</div>
		<?php
	}

	public function page_init() {
		register_setting(
			'my_option_group', // Option group
			'my_option_name', // Option name
			[$this, 'sanitize'] // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			'Domates Bağlantı', // Title
			[$this, 'print_section_info'], // Callback
			'domates-baglanti-ayar' // Page
		);

		add_settings_field(
			'id_number', // ID
			'Token Key', // Title
			[$this, 'id_number_callback'], // Callback
			'domates-baglanti-ayar', // Page
			'setting_section_id' // Section
		);


	}

	public function sanitize($input) {
		$new_input = [];
		if(isset($input['id_number']))
			$new_input['id_number'] = ($input['id_number']);

		return $new_input;
	}

	public function print_section_info() {
		print 'Sistemin bağlantı sağlayabilmesi için lütfen panel üzerinden aldığınız token keyi giriniz. Token keyiniz yok ise panel üzerinden edinebilirsiniz.';
	}

	public function id_number_callback() {
		printf(
			'<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
			isset($this->options['id_number']) ? esc_attr($this->options['id_number']) : ''
		);
	}
}

if(is_admin()) {
	$my_settings_page = new MySettingsPage();
}
    