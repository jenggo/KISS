<?php
				#Tampilkan halaman manajemen data_utama
				if ($halaman_login == 'data-utama') {
					if (isset($_POST['simpan']) AND !empty($_POST['title_blog'])) {
						$title = $this->filter($_POST['title_blog']);
						$slogan = (!empty($_POST['slogan_blog'])) ? $this->filter($_POST['slogan_blog']) : '';
						$meta_keywords = (!empty($_POST['meta_keywords'])) ? $this->filter($_POST['meta_keywords']) : '';
						$meta_description = (!empty($_POST['meta_description'])) ? $this->filter($_POST['meta_description']) : '';
						$wordpress_key = (!empty($_POST['wordpress_key'])) ? $this->filter($_POST['wordpress_key']) : '';
						$password_user = (!empty($_POST['password_user'])) ? $this->filter($_POST['meta_keywords']) : $this->data_utama['password_user'];

						$proses = $this->db->perbarui('utama', "
							title = '$title',
							slogan = '$slogan',
							meta_keywords = '$meta_keywords',
							meta_description = '$meta_description',
							wordpress_key = '$wordpress_key',
							password_user = '$password_user'
							", "id = 1");

						$konten .= ($proses) ? 'Data Utama berhasil diperbarui' : 'Gagal memperbarui Data Utama';
					}
					$konten .= '
						<form method="post" action="">
							<p>
								<label for="title_blog">Title Blog</label><br />
								<input type="text" name="title_blog" id="title_blog" maxlength="100" value="'.$this->data_utama['title'].'" />
							</p>
							<p>
								<label for="slogan_blog">Slogan Blog</label><br />
								<input type="text" name="slogan_blog" id="slogan_blog" maxlength="100" value="'.$this->data_utama['slogan'].'" />
							</p>
							<p>
								<label for="meta_keywords">Meta Keywords</label><br />
								<input type="text" name="meta_keywords" id="meta_keywords" maxlength="255" value="'.$this->data_utama['meta_keywords'].'" />
							</p>
							<p>
								<label for="meta_description">Meta Description</label><br />
								<input type="text" name="meta_description" id="meta_description" value="'.$this->data_utama['meta_description'].'" />
							</p>
							<p>
								<label for="wordpress_key">Wordpress Key (untuk Akismet)</label><br />
								<input type="text" name="wordpress_key" id="wordpress_key" maxlength="20" value="'.$this->data_utama['wordpress_key'].'" />
							</p>
							<p>
								<label for="password_user">Password (diisi jika ingin mengganti password)</label><br />
								<input type="password" name="password_user" id="password_user" />
							</p>
							<p class="no-border">
								<input type="submit" name="simpan" value="Simpan Data Utama" class="button" />
							</p>
						</form>
					';
				}

				# Tampilkan halaman manajemen kategori
				elseif ($halaman_login == 'kategori') {

					# Simpan / edit kedatabase kategori
					if (isset($_POST['simpan']) AND !empty($_POST['nama'])) {
						$nama = $this->filter($_POST['nama']);
						$slug = $this->slug($nama);
						$deskripsi = (!empty($_POST['deskripsi'])) ? $this->filter($_POST['deskripsi']) : '';

						if (!empty($_POST['id_kategori'])) {
							$id_kategori = $this->filter($_POST['id_kategori']);
							$proses = $this->db->perbarui('kategori_konten', "nama = '$nama', slug = '$slug', deskripsi = '$deskripsi'", "id = '$id_kategori'");
							$var_pesan = 'mengedit';
						}
						else {
							$proses = $this->db->tambah('kategori_konten', 'nama, slug, deskripsi', "'$nama', '$slug', '$deskripsi'");
							$var_pesan = 'membuat';
						}

						$konten .= ($proses) ? 'Berhasil '.$var_pesan.' kategori : '.$nama : 'Gagal '.$var_pesan.' kategori bernama '.$nama;
					}

					# Hapus kategori apabila tidak ada konten yang menggunakannya
					elseif (isset($_POST['hapus']) AND !empty($_POST['pilihan'])) {
						$num = 0;

						foreach ($_POST['pilihan'] as $id_kategori) {
							$id_kategori = $this->filter($id_kategori);
							$cek_konten = $this->db->queri("SELECT id FROM konten WHERE id_kategori = '$id_kategori'", 1);
							$proses = (!empty($cek_konten)) ? $num-- : $this->db->hapus('kategori_konten', "id = '$id_kategori'");
							$num++;
						}

						$konten .= ($proses) ? 'Berhasil menghapus '.$num.' kategori' : 'Gagal menghapus kategori';
					}

					# Edit konten, ambil data dan masukkan ke form pembuatan
					if (isset($_POST['edit']) AND !empty($_POST['pilihan'])) {
						if (count($_POST['pilihan']) > 1)
							$konten .= 'Tidak ada fitur multiple-edit!';
						else {
							foreach ($_POST['pilihan'] as $id_kategori) {
								$id_kategori = $this->filter($id_kategori);
								$data_kategori = $this->db->ambil('kategori_konten', 'id, nama, deskripsi', "id = '$id_kategori'");
							}

							$nama = $data_kategori['nama'];
							$deskripsi = $data_kategori['deskripsi'];
							$form_id = '<input type="hidden" name="id_kategori" value="'.$data_kategori['id'].'" />';
							$value = 'Simpan Editan';
						}
					}
					else {
						$nama = '';
						$deskripsi = '';
						$form_id = '';
						$value = 'Buat Baru';
					}


					$list_kategori = @$this->db->tabel('kategori_konten', 'id, nama, deskripsi', "WHERE id != ''");

					if (!$list_kategori)
						$konten .= 'Kategori Kosong!';
					else {
						$konten .= '
						<form method="post" action="">
							<p>
								<label for="nama">Nama Kategori</label><br />
								<input type="text" name="nama" id="nama" maxlength="30" value="'.$nama.'" />
							</p>
							<p>
								<label for="deskripsi">Deskripsi</label><br />
								<input type="text" name="deskripsi" id="deskripsi" value="'.$deskripsi.'" />
							</p>
							<p class="no-border">
								'.$form_id.'<input type="submit" name="simpan" value="'.$value.'" class="button" />
							</p>
						</form>

						<form method="post" action="">
							<table class="clear">
								<thead>
									<th><input type="checkbox" name="pilih" id="pilih" /></th>
									<th>Nama</th>
									<th>Deskripsi</th>
								</thead>
								<tbody>';
									$num = 0;
									foreach ($list_kategori as $lk) {
										$class = (($num%2) != 0) ? '' : ' class="altrow"';
										$konten .= '
										<tr'.$class.'>
											<td><input type="checkbox" name="pilihan[]" value="'.$lk['id'].'" class="pilihan" /></td>
											<td>'.$lk['nama'].'</td>
											<td>'.$lk['deskripsi'].'</td>
										</tr>';
										$num++;
									}
									$konten .= '
								</tbody>
							</table>
							<p class="no-border">
								<input type="submit" name="edit" value="Edit" class="button" /> <input type="submit" name="hapus" value="Hapus" class="button" />
							</p>
						</form>';
					}
				}

				# Tampilkan halaman manajemen konten / pages
				elseif ($halaman_login == 'konten' OR $halaman_login == 'pages') {

					# Bila pages beri nilai 1, konten 0.
					$tipe = ($halaman_login == 'pages') ? 1 : 0;

					# Variabel jenis sesuaikan dengan tipe konten
					$jenis = ($tipe == 1) ? 'pages' : 'konten';

					# Simpan konten / pages (baru maupun editan)
					if (isset($_POST['simpan']) AND !empty($_POST['judul']) AND !empty($_POST['isi'])) {
						$judul = $this->filter($_POST['judul']);
						$isi = $this->filter($_POST['isi']);
						$kategori = (!empty($_POST['kategori_konten'])) ? $this->filter($_POST['kategori_konten']) : 0;
						$tanggal = date('j M Y');
						$slug = $this->slug($judul);

						if (!empty($_POST['id_konten'])) {
							# Simpan Editan
							$id_konten = $this->filter($_POST['id_konten']);
							$proses = $this->db->perbarui('konten', "id_kategori = '$kategori', judul = '$judul', slug = '$slug', isi = '$isi'", "id = '$id_konten'");
						}
						else
							# Simpan konten / pages baru
							$proses = $this->db->tambah('konten', 'id_kategori, tanggal, judul, slug, isi, pages', "$kategori, '$tanggal', '$judul', '$slug', '$isi', $tipe");

						$konten .= ($proses) ? $judul.' berhasil disimpan' : 'Gagal menyimpan '.$judul;
					}

					# Hapus konten / pages
					elseif (isset($_POST['hapus']) AND !empty ($_POST['pilihan'])) {
						$num = 0;
						foreach ($_POST['pilihan'] as $id_konten) {
							$id_konten = $this->filter($id_konten);
							$proses = $this->db->hapus('konten', "id = '$id_konten'");
							$num++;
						}

						$konten .= ($proses) ? 'Menghapus '.$num.' '.$jenis : 'Gagal menghapus '.$jenis;
					}

					# Ubah konten menjadi pages
					elseif (isset($_POST['ubah']) AND !empty($_POST['pilihan'])) {
						$num = 0;
						foreach ($_POST['pilihan'] as $id_konten) {
							$id_konten = $this->filter($id_konten);
							$proses = $this->db->perbarui('konten', "pages = 1", "id = '$id_konten'");
							$num = ($proses) ? $num + 1 : $num - 1;
						}

						$konten .= ($proses) ? 'Mengubah '.$num.' konten menjadi pages' : 'Hanya berhasil mengubah '.$num.' menjadi pages';
					}

					# Editing konten / pages dan masukkan pada form dibawah
					if (isset($_POST['edit']) AND !empty($_POST['pilihan'])) {
						if (count($_POST['pilihan']) > 1)
							$konten .= 'Tidak ada fitur multiple-edit';
						else {
							foreach ($_POST['pilihan'] as $id_konten) {
								$id_konten = $this->filter($id_konten);
							}
							$data_konten_edit = @$this->db->ambil('konten', 'id_kategori, judul, isi', "id = '$id_konten'");
							if ($data_konten_edit) {
								$judul_edit = $data_konten_edit['judul'];
								$isi_edit = str_replace('<br />', '', $data_konten_edit['isi']); # Buang tag '<br />', fix untuk smarkup
								$tambahan_form = '<input type="hidden" name="id_konten" value="'.$id_konten.'" />'; # Masukkan id konten kedalam form
							}
						}
					}
					else {
						$judul_edit = '';
						$isi_edit = '';
						$tambahan_form = '';
					}

					# Bila menampilkan konten ambil dahulu data kategori untuk dipakai pada form pembuatan / editing konten
					if ($tipe == 0) {
						$kategori = $this->db->tabel('kategori_konten', 'id, nama', "WHERE id != ''");

						$form_kategori = '
						<p>
							<label for="kategori_konten">Kategori</label><br />
							<select name="kategori_konten" id="kategori_konten">';

							foreach ($kategori as $k) {

								# Jika user mengedit konten, tampilkan kategori yang dipilih oleh konten
								if (isset($_POST['edit']) AND !empty($data_konten_edit))
									$selected = ($k['id'] == $data_konten_edit['id_kategori']) ? ' selected="selected"' : '';
								else
									$selected = '';

								$form_kategori .= '<option value="'.$k['id'].'"'.$selected.'>'.$k['nama'].'</option>';
							}

							$form_kategori .='
							</select>
						</p>';
					}
					else
						$form_kategori = '';

					# Tampilkan form pembuatan konten / pages
					$konten .= '
						<form method="post" action="">
							<p>
								<label for="judul">Judul</label><br />
								<input type="text" name="judul" id="judul" maxlength="50" value="'.$judul_edit.'" />
							</p>
							'.$form_kategori.'
							<p>
								<label for="isi">Isi</label><br />
								<textarea name="isi" id="isi" rows="19" cols="30">'.$isi_edit.'</textarea>
							</p>
							<p class="no-border">
								<input type="submit" name="simpan" value="Simpan" class="button" />'.$tambahan_form.'
							</p>
						</form>
					';

					# Tampilkan tabel list konten / pages
					# Pertama-tama atur dahulu variabel untuk pagination
					$jumlah_konten = @$this->db->queri("SELECT id FROM konten WHERE pages = $tipe", 1); # Hitung berapa banyak jumlah konten
					$limit = 15; # 15 konten / pages per-tabel
					$page = (empty($_GET['halaman'])) ? 1 : $this->filter($_GET['halaman']); # Ambil variabel halaman, jika kosong dianggap halaman 1
					$start = $limit * ($page - 1);

					# Ambil data dari database
					$list_konten_atau_pages = @$this->db->tabel('konten', 'id, id_kategori, tanggal, judul, slug, pages', "WHERE pages = $tipe LIMIT $start, $limit");

					# Tampilkan ketabel apabila data tidak kosong
					if ($list_konten_atau_pages) {
						$konten .= '
						<form method="post" action="">
							<table class="clear">
								<thead>
									<th><input type="checkbox" name="pilih" id="pilih" /></th>
									<th>Tanggal</th>
									<th>Kategori</th>
									<th>Judul</th>
								</thead>
								<tbody>';

								$num = 0;
								foreach ($list_konten_atau_pages as $lkp) {
									$class = (($num%2) != 0) ? '' : ' class="altrow"';

									# Jika pages jangan proses kategori dan buat url sesuai format
									if ($tipe == 1) {
										$kategori = '-';
										$alamat = $this->alamat.'/'.$lkp['slug'];
										$tombol_ubah = '';
									}
									# Jika konten maka ambil data kategori, format url, dan tambahkan tombol untuk mengubahnya ke pages
									else {
										$data_kategori = $this->db->ambil('kategori_konten', 'nama, slug', "id = '".$lkp['id_kategori']."'");
										$kategori = $data_kategori['nama'];
										$alamat = $this->alamat.'/'.$kategori['slug'].'/'.$lkp['slug'];
										$tombol_ubah = '<input type="submit" name="ubah" value="Ubah menjadi pages" class="button" />';
									}

									$tanggalnya = $this->tanggal($lkp['tanggal']);


									$konten .= '
										<tr'.$class.'>
											<td><input type="checkbox" name="pilihan[]" value="'.$lkp['id'].'" class="pilihan" /></td>
											<td>'.$tanggalnya.'</td>
											<td>'.$kategori.'</td>
											<td>'.$lkp['judul'].'</td>
										</tr>
									';
								}

								# Pagination
								$pagination = $this->pagination($jumlah_konten, $limit, 2, $page, $this->alamat.'/'.$this->link_menu_login.'/'.$jenis, '/halaman/');

								$konten .= '
								</tbody>
							</table>'.$pagination.'
							<p class="no-border">
								<input type="submit" name="hapus" value="Hapus" class="button" /> <input type="submit" name="edit" value="Edit" class="button" /> '.$tombol_ubah.'
							</p>
						</form>';
					}
				}

				#Tampilkan halaman manajemen komentar
				elseif ($halaman_login == 'komentar') {
					# Proses komentar yang ditandai sebagai SPAM
					if (isset($_POST['spam']) AND !empty($_POST['pilihan'])) {
						# Panggil classAkismet
						require_once($this->direktori_kiss.'/classAkismet.php');

						foreach ($_POST['pilihan'] as $id_komentar) {
							$id_komen = $this->filter($id_komentar);

							# Jangan tampilkan komentar dari konten yang bersangkutan
							$proses = $this->db->perbarui('komentar', "aktif = 0", "id = '$id_komentar'");

							if ($proses) {
								# Ambil data komentar
								$komen_spam = $this->db->ambil('komentar', 'komentar, nama, email, situs', "id = '$id_komentar'");

								# Proses dengan Akismet (submit ke server Akismet sebagai SPAM)
								$akismet = new Akismet($this->alamat ,$this->data_utama['wordpress_key']);
								$akismet->setCommentAuthor($komen_spam['nama']);
								$akismet->setCommentAuthorEmail($komen_spam['email']);

								if (!empty($komen_spam['situs']))
									$akismet->setCommentAuthorURL($komen_spam['situs']);

								$akismet->setCommentContent($komen_spam['komentar']);
								$akismet->submitSpam();
							}
						}

						$komen .= 'Komentar telah ditandai sebagai SPAM';
					}

					# Hapus komentar
					elseif (isset($_POST['hapus']) AND !empty($_POST['pilihan'])) {

						$num = 0;
						foreach ($_POST['pilihan'] as $id_komentar) {
							$id_komentar = $this->filter($id_komentar);
							$proses = $this->db->hapus('komentar', "id = '$id_komentar'");
							$num++;
						}

						$konten .= ($proses) ? 'Menghapus '.$num.' komentar' : 'Gagal menghapus komentar';
					}

					# Tampilkan tabel komentar
					# Pertama-tama set dahulu variable pagination
					$jumlah_komentar = @$this->db->queri("SELECT id FROM komentar", 1); # Hitung jumlah komentar
					$limit = 25; # 25 komentar per-tabel
					$page = (empty($_GET['halaman'])) ? 1 : $this->filter($_GET['halaman']); # Ambil variabel halaman, jika kosong dianggap halaman 1
					$start = $limit * ($page - 1);

					$list_komentar = @$this->db->tabel('komentar', '*', "WHERE id != '' ORDER BY aktif, tanggal DESC LIMIT $start, $limit");

					if (!$list_komentar)
						$konten .= 'Komentar masih kosong';
					else {
						$konten .= '
						<form method="post" action="">
						<table class="clear">
							<thead>
								<th><input type="checkbox" name="pilih" id="pilih" /></th>
								<th>Tanggal</th>
								<th>Konten</th>
								<th>Nama</th>
								<th>Komentar</th>
								<th>Status</th>
							</thead>
							<tbody>';
							$num = 0;
							foreach ($list_komentar as $lk) {
								$class = (($num%2) != 0) ? '' : ' class="altrow"';
								$tanggalnya = $this->tanggal($lk['tanggal']);
								$data_konten = $this->db->ambil('konten', 'judul, slug', "id = '".$lk['id_konten']."'");
								$alamat_komen = $this->alamat.'/lihat-komen/'.$data_konten['slug'].'#komentar-'.$lk['id'];
								$status = ($lk['aktif'] == 1) ? 'Aktif' : 'SPAM';
								$konten .= '
									<tr'.$class.'>
										<td><input type="checkbox" name="pilihan[]" value="'.$lk['id'].'" class="pilihan" /></td>
										<td>'.$tanggalnya.'</td>
										<td><a href="'.$alamat_komen.'" title="Lihat Komentar">'.$data_konten['judul'].'</a></td>
										<td>'.$lk['nama'].'</td>
										<td>'.$this->html_substr($lk['komentar'], 200).'</td>
										<td>'.$status.'</td>
									</tr>
								';
								$num++;
							}

							# Pagination
							$pagination = $this->pagination($jumlah_komentar, $limit, 2, $page, $this->alamat.'/'.$this->link_menu_login.'/komentar', '/halaman/');

							$konten .= '
							</tbody>
						</table>'.$pagination.'
						<p class="no-border">
							<input type="submit" name="spam" value="Tandai SPAM" class="button" /> <input type="submit" name="hapus" value="Hapus" class="button" />
						</p>
						</form>';
					}
				}

				elseif ($halaman_login == 'logout') {
					session_destroy();
					$konten .= '<script type="text/javascript">window.location = "'.$this->alamat.'"</script>';
				}
?>