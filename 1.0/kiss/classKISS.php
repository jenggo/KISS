<?php
	/*
	 * KISS, KISS Is Simple yet Small. (Maksa yak? Hehe..)
	 * Versi 1.0
	 * F.Farwan (http://jenggo.net)
	 * 14 Oktober 2009
	 *
	 * KISS adalah aplikasi blog sederhana yang menggunakan database SQLite.
	 * Fitur :
	 * 1. Hanya untuk 1 blogger
	 * 2. Clean URL / Permalink dari awal
	 * 3. Hanya bisa posting konten, pages, dan mengisi komentar
	 * 4. Komentar diproteksi oleh anti-spam Akismet dan dilengkapi dengan Gravatar
	 * 5. Filter input dari XSS (filter diambil dari CMS SNews - http://snewscms.com)
	 *
	 *  The MIT License

		Copyright (c) 2009 Fakhriza Farwan

		Permission is hereby granted, free of charge, to any person obtaining a copy
		of this software and associated documentation files (the "Software"), to deal
		in the Software without restriction, including without limitation the rights
		to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
		copies of the Software, and to permit persons to whom the Software is
		furnished to do so, subject to the following conditions:

		The above copyright notice and this permission notice shall be included in
		all copies or substantial portions of the Software.

		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
		IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
		FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
		AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
		LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
		OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
		THE SOFTWARE.
	*/

	class kiss {

		public $data_utama;
		public $alamat;
		public $direktori;
		public $db;
		private $direktori_kiss;
		private $login;
		private $link_menu_login;

		protected function pesanError($pesan) {
			return 'ERROR: '.$pesan;
		}

		public function __construct($alamat_host_default, $direktori_host_default, $folder_kiss_default, $alamat_login_default) {
			# Bila session aktif, buat baru untuk mencegah serangan session fixation
			if ($_SESSION)
				session_regenerate_id(true);

			# Pakai fungsi "compression" PHP, selain mengecilkan ukuran juga memperbolehkan kita mengubah HEADER sebelum dikirim ke browser
			ob_start();

			# Definisikan alamat serta path
			$this->alamat = $alamat_host_default;
			$this->direktori = $direktori_host_default;
			$this->direktori_kiss = $folder_kiss_default;
			$this->login = $alamat_login_default;

			# Inisialisasi database, nama database adalah kiss.db
			$this->db = new db('kiss.db');

			# Cek variabel data_utama, bila kosong masukkan data dari database, jika tidak bisa berarti KISS belum pernah dipakai
			if (empty($this->data_utama)) {
				$this->data_utama = @$this->db->ambil('utama', '*', "id = 1");

				if (!$this->data_utama) {
					# Lakukan instalasi database
					require_once($this->direktori_kiss.'/instalasi.php');
				}
			}
		}

		# Fungsi untuk menampilkan header
		public function head() {
			echo '
				<meta name="description" content="',$this->data_utama['meta_description'],'" />
				<meta name="keywords" content="',$this->data_utama['meta_keywords'],'" />
				<title>',$this->data_utama['title'],' - ',$this->data_utama['slogan'],'</title>
				<link rel="stylesheet" type="text/css" media="screen" href="'.$this->alamat.'/'.$this->direktori_kiss.'/css/pagination.css" />
			';
		}

		# Fungsi untuk menampilkan menu
		public function menu($link_menu_administrator = 'manajemen') {
			# Set menu login sesuai variabel
			$this->link_menu_login = $link_menu_administrator;

			$menu = '<li><a href="'.$this->alamat.'" title="Home" rel="nofollow">Home</a></li>';

			# Tampilkan menu yang berbeda saat login menjadi administrator
			if (!empty($_SESSION['login'])) {
				$menu_login = array(
					'Data' => 'data-utama',
					'Kategori' => 'kategori',
					'Konten' => 'konten',
					'Pages' => 'pages',
					'Komentar' => 'komentar',
					'Logout' => 'logout'
				);

				$set_menu_login = (isset($_GET[$this->link_menu_login]) AND !empty($_GET[$this->link_menu_login])) ? $this->filter($_GET[$this->link_menu_login]) : '';

				foreach ($menu_login as $nama => $slugmenu) {
					$selected = ($set_menu_login == $slugmenu) ? ' id="current"' : '';
					$menu .= '<li'.$selected.'><a href="'.$this->alamat.'/'.$this->link_menu_login.'/'.$slugmenu.'" title="'.$nama.'">'.$nama.'</a></li>';
				}
			}

			# Jika tidak login, tampilkan menu standar
			else {
				# Ambil data pages dan tampilkan sebagai menu
				$menu_pages = $this->db->tabel('konten', 'judul, slug', "WHERE pages == 1");
				foreach ($menu_pages as $mp)
					$menu .= '<li><a href="'.$this->alamat.'/pages/'.$mp['slug'].'" title="'.$mp['judul'].'" >'.$mp['judul'].'</a></li>';
			}

			echo $menu;
		}

		# Fungsi untuk menampilkan isi blog
		public function body($limit = 4) {
			# Set variabel kosong sebagai awal
			$konten = '';

			# Tampilkan halaman login, jika diminta
			if (isset($_GET[$this->login])) {

				# Apabila belum login, tampilkan form
				if (empty($_SESSION['login'])) {
					$konten .= '
						<form method="post" action="">
							<p>
								<label for="password">Password Login</label>
								<input type="password" name="password" id="password" />
							</p>
							<p class="no-border">
								<input type="submit" name="login" value="Login" class="button" />
							</p>
						</form>
					';

					if (isset($_POST['login']) AND !empty($_POST['password'])) {
						$password = sha1($_POST['password']);
						if ($this->data_utama['password_user'] == $password) {
							$_SESSION['login'] = md5(rand());
							$konten .= '
								Login berhasil! Klik pada Home untuk masuk ke halaman Administrator
								<script type="text/javascript">window.location = "'.$this->alamat.'"</script>
							';
						}
						else
							$konten .= 'Password salah!';
					}
				}
				# Bila sudah login redirect ke halaman depan
				else
					$konten .= '<script type="text/javascript">window.location = "'.$this->alamat.'"</script>';
			}

			#Tampilkan halaman administrator bila diminta, user harus login terlebih dahulu
			elseif (isset($_GET[$this->link_menu_login]) AND !empty($_GET[$this->link_menu_login]) AND !empty($_SESSION['login'])) {
				$halaman_login = $this->filter($_GET[$this->link_menu_login]);
				require_once($this->direktori_kiss.'/administrasi.php');
			}

			# Tampilkan Pages
			elseif (isset($_GET['pages']) AND !empty($_GET['pages'])) {
				$slug = $this->filter($_GET['pages']);

				$pages = $this->db->ambil('konten', 'tanggal, judul, isi', "pages == 1 AND slug = '$slug'");

				if ($pages) {
					$tanggalnya = $this->tanggal($pages['tanggal']);
					$konten .= '<h2>'.$pages['judul'].'</h2><p class="post-info">'.$tanggalnya.'</p><p>'.$pages['isi'].'</p>';
				}
				else
					$konten .= 'Tidak ditemukan pages dengan judul tersebut!';
			}

			# Tampilkan konten
			elseif (isset($_GET['konten']) AND !empty($_GET['konten'])) {
				$slugkonten = $this->filter($_GET['konten']);
				$data_konten = @$this->db->ambil('konten', 'id, id_kategori, tanggal, judul, isi', "pages != 1 AND slug = '$slugkonten'");

				if ($data_konten) {
					$tanggalnya = $this->tanggal($data_konten['tanggal']);

					$kategorinya = $this->db->ambil('kategori_konten', 'nama, slug', "id = '".$data_konten['id_kategori']."'");
					$kategori = 'Kategori : <a href="'.$this->alamat.'/kategori/'.$kategorinya['slug'].'" title="Kategori '.$kategorinya['nama'].'">'.$kategorinya['nama'].'</a>';

					$cek_komentar = $this->db->queri("SELECT id FROM komentar WHERE id_konten = '".$data_konten['id']."'", 1);
					$jumlah_komentar = ($cek_komentar) ? $cek_komentar : 0;

					$konten .= '
						<h2>'.$data_konten['judul'].'</h2>
						<p class="post-info">'.$tanggalnya.' | '.$kategori.'</p>
						<p>'.$data_konten['isi'].'</p>
						<h3 id="komentar">'.$jumlah_komentar.' komentar</h3>';

					if (isset($_POST['post_komentar']) AND !empty($_POST['nama']) AND !empty($_POST['mail']) AND !empty($_POST['komentar']))
						$this->proses_komentar($data_konten['id'], $_POST['komentar'], $_POST['nama'], $_POST['mail'], $_POST['situs']);

					if ($jumlah_komentar > 0) {
						# Masukkan file classGravatar
						require_once($this->direktori_kiss.'/classGravatar.php');

						$komen = '<ol class="commentlist">';

						$list_komen = $this->db->tabel('komentar', 'id, tanggal, komentar, nama, situs', "WHERE id_konten = '".$data_konten['id']."'");

						$num = 0;
						foreach ($list_komen as $lk) {
							$class = (($num%2) != 0) ? '' : ' class="alt"';

							if ($_SERVER['HTTP_HOST'] != 'localhost') {
								$gravatar = new Gravatar($lk['email'], $grafdef);
								$gravatar->size = 40;
								$gravatar->rating = "G";
								$gravatar->border = "d6d6d6";
							}
							else
								$gravatar = '<img src="'.$this->alamat.'/'.$this->direktori_kiss.'/images/gravatar.jpg" alt="Gravatar" class="gravatar" />';

							$user = (!empty($lk['situs'])) ? '<a href="'.$lk['situs'].'" title="Situs '.$lk['nama'].'">'.$lk['nama'].'</a>' : $lk['nama'];

							$komen .= '
								<li'.$class.' id="komentar-'.$lk['id'].'">
									<cite>
										'.$gravatar.$user.'<br />
										<span class="comment-data">'.$lk['tanggal'].'</span>
									</cite>
									<div class="comment-text"><p>'.$lk['komentar'].'</p></div>';

							$num++;
						}
						$konten .= $komen.'</ol>';
					}

					$konten .= '
						<form method="post" action="" class="commentform">
							<p>
								<label for="nama">Nama</label><br />
								<input type="text" name="nama" id="nama" tabindex="1" />
							</p>
							<p>
								<label for="mail">Alamat Email</label><br />
								<input type="text" name="mail" id="mail" tabindex="2" />
							</p>
							<p>
								<label for="situs">Alamat Situs</label><br />
								<input type="text" name="situs" id="situs" tabindex="3" />
							</p>
							<p>
								<label for="komentar">Komentar</label><br />
								<textarea name="komentar" id="komentar" rows="10" cols="19" tabindex="4"></textarea>
							</p>
							<p class="no-border">
								<input type="submit" name="post_komentar" value="Submit Komentar" class="button" tabindex="5" />
							</p>
						</form>
					';
				}
				else
					$konten .= 'Tidak menemukan konten!';
			}

			# Tampilkan halaman depan
			else {
				$jumlah_konten = @$this->db->queri("SELECT id FROM konten WHERE pages != 1", 1);
				if ($jumlah_konten) {

					# Mulai buat variabel yang dibutuhkan oleh pagination
					$page = (!empty($_GET['halaman'])) ? $this->filter($_GET['halaman']) : 1;
					$start = $limit * ($page - 1);

					$cek = $this->db->tabel('konten', 'id, id_kategori, tanggal, judul, slug, isi', "WHERE pages != 1 ORDER BY tanggal DESC LIMIT $start, $limit");
					foreach ($cek as $c) {

						$kategorinya = $this->db->ambil('kategori_konten', 'nama, slug', "id = '".$c['id_kategori']."'");
						$kategori = 'Kategori : <a href="'.$this->alamat.'/kategori/'.$kategorinya['slug'].'" title="Kategori '.$kategorinya['nama'].'">'.$kategorinya['nama'].'</a>';

						$alamat_konten = $this->alamat.'/'.$kategorinya['slug'].'/'.$c['slug'];

						$cek_komentar = $this->db->queri("SELECT id FROM komentar WHERE id_konten = '".$c['id']."'", 1);
						$jumlah_komentar = ($cek_komentar) ? $cek_komentar : 0;
						$komentar = $jumlah_komentar.' <a href="'.$alamat_konten.'#komentar" title="Komentar dari konten '.$c['judul'].'" rel="nofollow">Komentar</a>';

						$tanggalnya = $this->tanggal($c['tanggal']);

						$konten .= '
							<h2><a href="'.$alamat_konten.'" title="Konten : '.$c['judul'].'">'.$c['judul'].'</a></h2>
							<p class="post-info">'.$kategori.'</p>
							<p>'.$c['isi'].'</p>
							<p class="postmeta">'
								.$komentar.' |
								<span class="date">'.$tanggalnya.'</span>';
					}

					$konten .= $this->pagination($jumlah_konten, $limit, 2, $page, $this->alamat, '/halaman/', 1);
				}
				else
					$konten .= '<h2>Masih Kosong</h2>Belum ada konten sama sekali';
			}

			echo (!empty($konten)) ? $konten : '404!';
		}

		# Fungsi untuk memproses form komentar
		protected function proses_komentar($id_konten, $komentar ,$nama, $email, $situs) {
			# Filtering input
			$id_konten = $this->filter($id_konten);
			$komentar = $this->filter($komentar);
			$nama = $this->filter($nama);
			$email = $this->filter($email);
			$tanggal = date("j M Y");

			# Cek variabel situs
			$situs = (!empty($situs)) ? 'http://'.str_replace('http://', '', $this->filter($situs)) : '';

			# Cek apakah komentar sudah dimasukkan sebelumnya
			$cekdobel = $this->db->queri("SELECT id_konten, komentar, nama, email FROM komentar WHERE id_konten = '$id_konten' AND nama = '$nama' AND email = '$email' AND komentar = '$komentar'", 1);
			if ($cekdobel > 0)
				echo 'Komentar sudah dimasukkan';
			else {
				if ($_SERVER['HTTP_HOST'] != 'localhost' AND !empty($this->data_utama['wordpress_key'])) {
					$konten = $this->db->ambil('konten', 'id_kategori, slug', "id = '$id_konten'");
					$kategori = $this->db->ambil('kategori', 'slug', "id = '".$konten['id_kategori']."'");
					$alamat_konten = $this->alamat.'/'.$kategori['slug'].'/'.$konten['slug'];

					$aktif = 1;

					# Panggil classAkismet
					require_once($this->direktori_kiss.'/classAkismet.php');

					$akismet = new Akismet($this->alamat, $this->data_utama['wordpress_key']);
					$akismet->setCommentAuthor($nama);
                    $akismet->setCommentAuthorEmail($email);
                    $akismet->setCommentAuthorURL($situs);
                    $akismet->setCommentContent($komentar);
                    $akismet->setPermalink($alamat_konten);

                    if($akismet->isCommentSpam()) {
                    	echo 'Komentar dikenali sebagai SPAM!';
                        $aktif = 0;
                    }
                    else {
                    	$proses = $this->db->tambah('komentar', 'tanggal, id_konten, komentar, nama, email, situs, aktif', "'$tanggal', $id_konten, '$komentar', '$nama', '$email', '$situs', $aktif");
                    	echo 'Terimakasih atas komentarnya '.$nama;
                    }
				}
				else
					$proses = $this->db->tambah('komentar', 'tanggal, id_konten, komentar, nama, email, situs, aktif', "'$tanggal', $id_konten, '$komentar', '$nama', '$email', '$situs', 1");
			}
		}

		# Fungsi untuk menampilkan list komentar
		public function list_komentar($jumlah_list) {
			# Ambil list komentar terakhir dan batasi sesuai variabel jumlah_list
			$list_komentar = @$this->db->tabel('komentar', 'id_konten, nama, situs', "WHERE aktif = 1 ORDER BY id DESC LIMIT $jumlah_list");
			if (!$list_komentar)
				echo '<li>Komentar masih kosong</li>';
			else {
				$list = '';

				foreach ($list_komentar as $lk) {
					# Ambil data konten yang berkaitan dengan komentar
					$konten = $this->db->ambil('konten', 'id_kategori, judul, slug', "id = '".$lk['id_konten']."'");

					# Ambi data kategori yang berkaitan dengan konten diatas
					$kategori = $this->db->ambil('kategori_konten', 'slug', "id = '".$konten['id_kategori']."'");

					# Buat alamat konten
					$alamat_konten = $this->alamat.'/'.$kategori['slug'].'/'.$konten['slug'];

					# Apabila komentator memiliki situs sertakan situsnya
					$komentator = (!empty($lk['situs'])) ? '<a href="'.$lk['situs'].'" title="Situs '.$lk['nama'].'">'.$lk['nama'].'</a>' : $lk['nama'];

					$list .= '<li>'.$komentator.' mengomentari <a href="'.$alamat_konten.'" title="Konten : '.$konten['judul'].'">'.$konten['judul'].'</a></li>';
				}
				echo $list;
			}
		}

		# Fungsi untuk memformat tanggal sesuai standar PHP
		protected function tanggal($input, $format = "j M Y") {
			return date($format, strtotime($input));
		}

		# Fungsi untuk mengembalikan hasil kompresi
		# Secara default, kompresi optimal tidak diaktifkan karena sering bermasalah dengan struktur html & css
		# Coba set variabel menjadi 1, selesai(1), apabila tampilan jadi rusak kosongkan variabel
		public function selesai($kompres = 0) {
			if ($kompres == 1) {
				$buffer = ob_get_clean();
				$buffer = $this->compress($buffer);
				echo $buffer;
			}
			else
				ob_end_flush();
		}

		# Fungsi untuk menghilangkan spasi kosong
		public function compress($buffer) {
			$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
			$buffer = str_replace(array("", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
			return $buffer;
		}

		# Fungsi untuk menampilkan smiley
		public function smiley($input) {
			if (!empty($input)) {
				$dirgbr = $this->direktori_gambar;

				$smiley = array(
					':))' => '<img src="'.$dirgbr.'laugh.gif" alt="Laugh" />',
					':)' => '<img src="'.$dirgbr.'smile.gif" alt="Smile" />',
					':(' => '<img src="'.$dirgbr.'sad.gif" alt="Sad" />',
					':D' => '<img src="'.$dirgbr.'grin.gif" alt="Grin" />',
					':d' => '<img src="'.$dirgbr.'grin.gif" alt="Grin" />',
					';)' => '<img src="'.$dirgbr.'wink.gif" alt="Wink" />',
					';D' => '<img src="'.$dirgbr.'wink.gif" alt="Wink" />',
					';d' => '<img src="'.$dirgbr.'wink.gif" alt="Wink" />',
					':?' => '<img src="'.$dirgbr.'asking.gif" alt="Asking" />',
					'8)' => '<img src="'.$dirgbr.'cool.gif" alt="Cool" />',
					':p' => '<img src="'.$dirgbr.'tongue.gif" alt="Tongue" />',
					':P' => '<img src="'.$dirgbr.'tongue.gif" alt="Tongue" />'
				);
				$proses = str_replace(array_keys($smiley), array_values($smiley), $input);
				return $proses;
			}
			else
				$this->pesanError('smiley membutuhkan input!');
		}

		# Fungsi untuk mengubah kalimat menjadi bentuk clean-url / permalink
		public function slug($input) {
			if (!empty($input)) {
				$lowercase = strtolower(trim($input));
				$removeaccents = html_entity_decode(preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|ring);/', $lowercase, htmlentities($lowercase, ENT_COMPAT)));
				$removenonalphanumeric = preg_replace('/[^a-z0-9- ]/', '', $removeaccents);
				$subtitutedashes = str_replace(' ', '-', $removenonalphanumeric);
				$cleanall = str_replace(array('---', '--'), '-', $subtitutedashes);
				return $cleanall;
			}
			else
				$this->pesanError('slug membutuhkan input!');
		}

		# Fungsi pagination
		public function pagination($total, $limit, $adjacents, $page, $baseaddress, $pagesaying, $ext = null, $class = null) {
			if (!empty($total) AND !empty($limit) AND !empty($adjacents) AND !empty($page) AND !empty($baseaddress) AND !empty($pagesaying)) {
				$start = 0;
				$pagination = '';
				$ext = (!empty($ext)) ? $ext : '';
				$class = (!empty($class)) ? ' class="'.$class.'"' : '';

				if (!empty($page))
					$start = ($page - 1) * $limit;
				else
					$page = 1;

				$prev = $page - 1;
				$next = ($page == -1) ? $page + 2 : $page + 1;
				$lastpage = ceil($total/$limit);
				$lpm1 = $lastpage - 1;

				if($lastpage > 1) {
					$pagination .= '<div class="pagination">';

					if ($page > 1)
						$pagination.= '<a href="'.$baseaddress.$pagesaying.$prev.$ext.'" title="Halaman sebelumnya"'.$class.' rel="nofollow">&lt;&nbsp;prev</a>';

					if ($lastpage < 7 + ($adjacents * 2)) {
						for ($counter = 1; $counter <= $lastpage; $counter++)
							$pagination .= ($counter == $page) ? '<span class="current">'.$counter.'</span>' : '<a href="'.$baseaddress.$pagesaying.$counter.$ext.'" title="Halaman '.$counter.'"'.$class.' rel="nofollow">'.$counter.'</a>';
					}
					elseif ($lastpage > 5 + ($adjacents * 2)) {
						if($page < 1 + ($adjacents * 2)) {
							for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
								$pagination .= ($counter == $page) ? '<span class="current">'.$counter.'</span>' : '<a href="'.$baseaddress.$pagesaying.$counter.$ext.'" title="Halaman '.$counter.'"'.$class.' rel="nofollow">'.$counter.'</a>';

							$pagination.= "...";
							$pagination.= '<a href="'.$baseaddress.$pagesaying.$lpm1.$ext.'" title="Halaman '.$lpm1.'"'.$class.' rel="nofollow">'.$lpm1.'</a>';
							$pagination.= '<a href="'.$baseaddress.$pagesaying.$lastpage.$ext.'" title="Halaman '.$lastpage.'"'.$class.' rel="nofollow">'.$lastpage.'</a>';
						}
						elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
							$pagination.= '<a href="'.$baseaddress.$pagesaying.'1'.$ext.'" title="Halaman 1"'.$class.' rel="nofollow">1</a>';
							$pagination.= '<a href="'.$baseaddress.$pagesaying.'2'.$ext.'" title="Halaman 2"'.$class.' rel="nofollow">2</a>';
							$pagination.= "...";

							for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
								$pagination .= ($counter == $page) ? '<span class="current">'.$counter.'</span>' : '<a href="'.$baseaddress.$pagesaying.$counter.$ext.'" title="Halaman '.$counter.'"'.$class.' rel="nofollow">'.$counter.'</a>';

							$pagination.= "...";
							$pagination.= '<a href="'.$baseaddress.$pagesaying.$lpm1.$ext.'" title="'.$lpm1.'"'.$class.' rel="nofollow">'.$lpm1.'</a>';
							$pagination.= '<a href="'.$baseaddress.$pagesaying.$lastpage.$ext.'" title="Halaman '.$lastpage.'"'.$class.' rel="nofollow">'.$lastpage.'</a>';
						}
						else {
							$pagination.= '<a href="'.$baseaddress.$pagesaying.'1'.$ext.'" title="Halaman 1"'.$class.' rel="nofollow">1</a>';
							$pagination.= '<a href="' . $baseaddress . $pagesaying .'2'.$ext.'" title="Halaman 2"'.$class.' rel="nofollow">2</a>';
							$pagination.= "...";

							for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
								$pagination .= ($counter == $page) ? '<span class="current">'.$counter.'</span>' : '<a href="'.$baseaddress.$pagesaying.$counter.$ext.'" title="Halaman '.$counter.'"'.$class.' rel="nofollow">'.$counter.'</a>';
						}
					}

					$pagination .= ($page < $counter - 1) ? '<a href="'.$baseaddress.$pagesaying.$next.$ext.'" title="Halaman berikutnya"'.$class.' rel="nofollow">next&nbsp; &gt;</a>' : '<span class="disabled">next&nbsp;&gt;</span>';
					$pagination.= "</div>\n";
				}
				return $pagination;
			}
			else
				$this->pesanError('pagination butuh variabel-variabel!');
		}

		# Fungsi untuk memotong konten tanpa merusak tag html (bila ada)
		public function html_substr($text, $length = 500, $ending = '...', $exact = true) {

			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length)
				return $text;

			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			foreach ($lines as $line_matchings) {
				if (!empty($line_matchings[1])) {
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					}
					else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false)
							unset($open_tags[$pos]);
					}
					else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings))
						array_unshift($open_tags, strtolower($tag_matchings[1]));

					$truncate .= $line_matchings[1];
				}

				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					$left = $length - $total_length;
					$entities_length = 0;

					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							}
							else
								break;
						}
					}

					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					break;
				}
				else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}

				if($total_length>= $length) break;
			}

			if (!$exact) {
				$spacepos = strrpos($truncate, ' ');
					if (isset($spacepos))
						$truncate = substr($truncate, 0, $spacepos);
			}

			$truncate .= $ending;
			foreach ($open_tags as $tag)
				$truncate .= '</' . $tag . '>';

			return $truncate;
		}

		# Fungsi filtering XSS
		public function filter($input) {
			if (!empty($input)) {
				$XSS_cache = array();
				if (!empty($XSS_cache) AND array_key_exists($input, $XSS_cache))
					return $XSS_cache[$input];

				$source = html_entity_decode($input, ENT_QUOTES, 'ISO-8859-1');
				$source = preg_replace('/&#38;#(\d+);/me','chr(\\1)', $source);
				$source = preg_replace('/&#38;#x([a-f0-9]+);/mei','chr(0x\\1)', $source);

				while($source != $this->filterTags($source))
					$source = $this->filterTags($source);

				$source = nl2br($source);
				$XSS_cache[$input] = $source;
				return $source;
			}
			else
				$this->pesanError('filter XSS membutuhkan input!');
		}

		# Fungsi filtering tag
		protected function filterTags($source) {
			$ra1 = array('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml');
			$ra2 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base', 'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
			$tagBlacklist = array_merge($ra1, $ra2);
			$preTag = NULL;
			$postTag = $source;
			$tagOpen_start = strpos($source, '<');
			while($tagOpen_start !== FALSE) {
				$preTag .= substr($postTag, 0, $tagOpen_start);
				$postTag = substr($postTag, $tagOpen_start);
				$fromTagOpen = substr($postTag, 1);
				$tagOpen_end = strpos($fromTagOpen, '>');

				if ($tagOpen_end === false) break;

				$tagOpen_nested = strpos($fromTagOpen, '<');

				if (($tagOpen_nested !== false) AND ($tagOpen_nested < $tagOpen_end)) {
					$preTag .= substr($postTag, 0, ($tagOpen_nested+1));
					$postTag = substr($postTag, ($tagOpen_nested+1));
					$tagOpen_start = strpos($postTag, '<');
					continue;
				}

				$tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start + 1);
				$currentTag = substr($fromTagOpen, 0, $tagOpen_end);
				$tagLength = strlen($currentTag);

				if (!$tagOpen_end) {
					$preTag .= $postTag;
					$tagOpen_start = strpos($postTag, '<');
				}

				$tagLeft = $currentTag;
				$attrSet = array();
				$currentSpace = strpos($tagLeft, ' ');

				if (substr($currentTag, 0, 1) == '/') {
					$isCloseTag = TRUE;
					list($tagName) = explode(' ', $currentTag);
					$tagName = substr($tagName, 1);
				}
				else {
					$isCloseTag = FALSE;
					list($tagName) = explode(' ', $currentTag);
				}

				if ((!preg_match('/^[a-z][a-z0-9]*$/i',$tagName)) || (!$tagName) || ((in_array(strtolower($tagName), $tagBlacklist)))) {
					$postTag = substr($postTag, ($tagLength + 2));
					$tagOpen_start = strpos($postTag, '<');
					continue;
				}

				while ($currentSpace !== FALSE) {
					$fromSpace = substr($tagLeft, ($currentSpace+1));
					$nextSpace = strpos($fromSpace, ' ');
					$openQuotes = strpos($fromSpace, '"');
					$closeQuotes = strpos(substr($fromSpace, ($openQuotes+1)), '"') + $openQuotes + 1;

					if (strpos($fromSpace, '=') !== FALSE) {
						if (($openQuotes !== FALSE) AND (strpos(substr($fromSpace, ($openQuotes+1)), '"') !== FALSE))
							$attr = substr($fromSpace, 0, ($closeQuotes+1));
						else $attr = substr($fromSpace, 0, $nextSpace);
					}
					else
						$attr = substr($fromSpace, 0, $nextSpace);

					if (!$attr)
						$attr = $fromSpace;

					$attrSet[] = $attr;
					$tagLeft = substr($fromSpace, strlen($attr));
					$currentSpace = strpos($tagLeft, ' ');
				}

				$postTag = substr($postTag, ($tagLength + 2));
				$tagOpen_start = strpos($postTag, '<');
			}

			$preTag .= $postTag;
			return $preTag;
		}

	}

	class db {
		protected $_sqlite;

		/* Menghitung banyaknya query yang dilakukan */
		public $total_queries;

		/* Membuat koneksi dengan database, bila belum ada maka database otomatis dibuat */
		public function __construct($namadb) {
			$this->_sqlite = new sqlitedatabase($namadb, 0666);
		}

		/* Menampilkan pesan error */
		protected function fatalError($message) {
			$pesan = 'Error : ' . $message;
		}

		/* Mengambil sebuah data didalam tabel dan ditampilkan seluruh field dalam data tersebut dalam bentuk array */
		public function ambil($tabel, $field, $tambahan) {
			$queri = "SELECT $field FROM $tabel WHERE $tambahan";
			$proses = $this->_sqlite->query($queri);

			if (!$proses)
				return $this->fatalError('Tidak bisa mengambil data dari tabel '.$tabel);
			else
				$hasil = $proses->fetch();

			return ($hasil) ? $hasil : false;
		}

		/* Menampilkan data-data dalam tabel dalam bentuk array, biasanya digunakan untuk menampilkan tabel */
		public function tabel($tabel, $field, $tambahan) {
			$queri = "SELECT $field FROM $tabel $tambahan";
			$hasil = $this->_sqlite->arrayquery($queri);

			return ($hasil) ? $hasil : $this->fatalError('Gagal menampilkan data dari tabel '.$tabel);
		}

		/* Menambah data baru kedalam tabel */
		public function tambah($tabel, $field, $data) {
			$queri = "INSERT INTO $tabel ($field) VALUES ($data)";
			$hasil = $this->_sqlite->query($queri);

			return ($hasil) ? true : $this->fatalError('Gagal menambah data baru kedalam tabel '.$tabel);
		}

		/* Memperbarui data didalam tabel */
		public function perbarui($tabel, $perintah, $fieldpenanda) {
			$queri = "UPDATE $tabel SET $perintah WHERE $fieldpenanda";
			$hasil = $this->_sqlite->query($queri);

			return ($hasil) ? true : $this->fatalError('Gagal memperbarui data didalam tabel '.$tabel);
		}

		/* Menghapus data didalam tabel */
		public function hapus($tabel, $fieldpenanda) {
			$queri = "DELETE FROM $tabel WHERE $fieldpenanda";
			$hasil = $this->_sqlite->query($queri);

			return ($hasil) ? true : $this->fatalError('Gagal menghapus data dari tabel '.$tabel);
		}

		/* Melakukan query berdasarkan perintah SQL, apabila variabel $hitung tidak kosong maka akan menampilkan hasil berupa jumlah row dalam tabel */
		public function queri($queri, $hitung = null) {
			$hasil = $this->_sqlite->query($queri);
			if ($hitung)
				$hasil = $hasil->numrows();

			return ($hasil) ? $hasil : $this->fatalError('Gagal melakukan queri!');
		}

		/* Melakukan proses VACUUM kepada tabel */
		public function vakum($tabel) {
			return $this->_sqlite->vacuum($tabel);
		}
	}
?>