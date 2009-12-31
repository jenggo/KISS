<?php
	# Mulai session, penting untuk login
	session_start();

	# Definisikan folder KISS
	$folder_kiss = 'kiss';

	# Panggil class KISS
	require_once($folder_kiss.'/classKISS.php');

	# Inisialisasi class KISS dengan variabel alamat blog, direktori blog, direktori KISS, alamat login
	$kiss = new kiss('http://localhost/kisscms', '/home/jenggo/public_html/kisscms', $folder_kiss, 'login');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php $kiss->head(); ?>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<meta name="author" content="Erwin Aligam - styleshout.com" />
<meta name="robots" content="index, follow, noarchive" />
<meta name="googlebot" content="noarchive" />

<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $kiss->alamat; ?>/css/screen.css" />

</head>
<body>
<div id="wrap">

	<a name="top"></a>

	<!-- header -->
	<div id="header">

		<h1 id="logo-text"><a href="<?php echo $kiss->alamat; ?>" title=""><?php echo $kiss->data_utama['title']; ?></a></h1>
		<p id="intro"><?php echo $kiss->data_utama['slogan']; ?></p>

		<!-- navigation -->
		<div  id="nav">
			<ul>
				<?php $kiss->menu(); ?>
			</ul>
		</div>

	</div>
	<!-- header ends -->

	<!-- content starts -->
	<div id="content-outer" class="clear"><div id="content-wrap" >

		<div class="content">

			<!-- Tampilkan halaman utama, termasuk halaman login -->
			<div class="post">
				<?php $kiss->body(); ?>
			</div>

			<!-- columns starts -->
			<div class="columns">

				<!-- Contoh mengambil konten paling terakhir -->
				<div class="col">

					<?php
						# Ambil konten terakhir dibuat
						$konten_terakhir = $kiss->db->ambil('konten', 'id_kategori, judul, slug, isi', "pages != 1 ORDER BY id DESC");

						# Ambil "slug" kategori untuk dipakai nanti sebagai alamat
						$kategori_terakhir = $kiss->db->ambil('kategori_konten', 'slug', "id = '".$konten_terakhir['id_kategori']."'");

						# Buat alamat konten
						$alamat_terakhir = $kiss->alamat.'/'.$kategori_terakhir['slug'].'/'.$konten_terakhir['slug'];
					?>

					<h3><?php echo $konten_terakhir['judul']; ?></h3>

					<p>
						<?php
							# Potong konten agar tidak terlalu panjang, batasi hanya sepanjang 700 karakter
							$konten_terakhir = $kiss->html_substr($konten_terakhir['isi'], 700);
							echo $konten_terakhir;
						?>
					</p>

					<p><a class="more-link" href="<?php echo $alamat_terakhir; ?>">continue reading</a></p>

				</div>
				<!-- Selesai mengambil konten terakhir -->

				<!-- Contoh mengambil konten ketiga -->
				<div class="col">
					<?php
						# Ambil konten ketiga
						$konten_ketiga = $kiss->db->ambil('konten', 'id_kategori, judul, slug, isi', "pages != 1 LIMIT 1,3");

						# Ambil "slug" kategori untuk dipakai nanti sebagai alamat
						$kategori_ketiga = $kiss->db->ambil('kategori_konten', 'slug', "id = '".$konten_ketiga['id_kategori']."'");

						# Buat alamat konten
						$alamat_ketiga = $kiss->alamat.'/'.$kategori_ketiga['slug'].'/'.$konten_ketiga['slug'];
					?>

					<h3><?php echo $konten_ketiga['judul']; ?></h3>
					<p>
					<?php
						# Potong konten agar tidak terlalu panjang, batasi hanya sepanjang 500 karakter (default)
						$konten_ketiga = $kiss->html_substr($konten_ketiga['isi']);
						echo $konten_ketiga;
					?>
					</p>

					<p><a class="more-link" href="<?php echo $alamat_ketiga; ?>">continue reading</a></p>

				</div>

			<!-- columns ends -->
			</div>

		</div>

	<!-- content ends -->
	</div></div>

	<!-- footer starts -->
	<div id="footer-outer" class="clear"><div id="footer-wrap">

		<div class="content">

			<!-- columns starts -->
			<div class="columns">

				<div class="col">

					<!-- Mengambil dari pages default -->

					<h3>About</h3>
					<p>
						<a href="<?php $kiss->alamat; ?>pages/about"><img src="<?php $kiss->alamat; ?>images/gravatar.jpg" width="40" height="40" alt="firefox" class="float-left" /></a>
						<?php
							$database_about = $kiss->db->ambil('konten', 'isi', "slug='about'");
							$potong = $kiss->html_substr($database_about['isi'], 700);
							echo $potong;
						?>
						<a href="<?php $kiss->alamat; ?>pages/about">Learn more...</a>
					</p>

					<p class="copyright">
						&copy;2008 All your copyright info here &nbsp;
					</p>

				</div>

				<div class="col">

					<!-- Belum dibuat class khusus pencarian
					<h3>Search</h3>

					<form id="quick-search" action="index.html" method="get" >
						<p>
						<label for="qsearch">Search:</label>
						<input class="tbox" id="qsearch" type="text" name="qsearch" value="Search..." title="Start typing and hit ENTER" />
						<input class="btn" alt="Search" type="image" name="searchsubmit" title="Search" src="images/search.gif" />
						</p>
					</form>
					-->

					<!-- Tampilkan list komentar terakhir (10) -->
					<div class="footer-list">
						<ul>
							<?php $kiss->list_komentar(10); ?>
						</ul>
					</div>

				</div>

			<!-- columns ends -->
			</div>

		</div>

	<!-- footer ends -->
	</div></div>

	<div id="footer-bottom">
		<p>
			<a href="index.html">Home</a> |
			<a href="index.html">Sitemap</a> |
			<a href="index.html">RSS Feed</a> |
			<a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a> |
	   	<a href="http://validator.w3.org/check/referer">XHTML</a> |
			<a href="#top">Top</a>
			&nbsp;&nbsp;
			Design by : <a href="http://www.styleshout.com/">styleshout</a>
		</p>
	</div>

<!-- wrap ends -->
</div>
</body>
</html>
<?php $kiss->selesai() ?>