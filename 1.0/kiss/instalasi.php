<?php
			$tabel_utama = '
				CREATE TABLE utama (
					id INTEGER(1) DEFAULT 1 PRIMARY KEY,
					title VARCHAR(100),
					slogan VARCHAR(100),
					meta_keywords VARCHAR(255),
					meta_description TEXT,
					wordpress_key VARCHAR(20),
					password_user VARCHAR(40),
					about_me TEXT
				)';
			$tabel_kategori = '
				CREATE TABLE kategori_konten (
					id INTEGER PRIMARY KEY,
					nama VARCHAR(30),
					slug VARCHAR(30),
					deskripsi VARCHAR(150)
				)';
			$tabel_konten = '
				CREATE TABLE konten (
					id INTEGER PRIMARY KEY,
					id_kategori INTEGER,
					tanggal DATE,
					judul VARCHAR(50),
					slug VARCHAR(50),
					isi TEXT,
					pages BOOLEAN
				)';
			$tabel_komentar = '
				CREATE TABLE komentar (
					id INTEGER PRIMARY KEY,
					tanggal DATE,
					id_konten INTEGER,
					komentar TEXT,
					nama VARCHAR(50),
					email VARCHAR(100),
					situs VARCHAR(255),
					aktif BOOLEAN
				)';
			$password_user = sha1('kiss');
			$tanggal = date('j M Y');
			$data_utama = "INSERT INTO utama (id, title, slogan, meta_keywords, meta_description, password_user) VALUES (1, 'KISS', 'KISS Is Simple Slog', 'blog, simple', 'KISS Is Simple Slog', '$password_user')";
			$pages_about = "INSERT INTO konten (tanggal, judul, slug, isi, pages) VALUES ('$tanggal', 'About', 'about', 'Amet at magna varius justo ornare cubilia. Mi. Viverra tristique arcu sem eni at aenean. Morbi. Auctor. Eget risus tempor faucibus orci, mus etiam. Pede cras integer iaculis tristique odio. Gravida ad duis dictumst ullamcorper, pharetra vulputate ac, tristique velit. Eu, quisque quis, mus ve pede dapibus ad, nostra ve. Viverra, cras dis nam mus euismod libero nisl luctus laoreet. Mattis class faucibus ut, metus ve nisi proin habitasse vitae lobortis morbi. Vel. Ante tincidunt libero in at mus fringilla. Ornare. Porttitor. Nullam dolor parturient enim class faucibus penatibus, cras sodales ad. Dis cras maecenas nullam, primis pharetra hymenaeos sollicitudin adipiscing duis. Velit ve nostra dui, nullam mi, proin hac proin ridiculus interdum. Velit parturient enim netus vitae. Donec nonummy mauris, curae sem vulputate, congue fermentum placerat auctor ligula. Eget imperdiet at semper ac pulvinar cras eni ve nisl. Tellus diam imperdiet taciti, ultrices habitasse quis.', 1)";
			$kategori_awal = "INSERT INTO kategori_konten (nama, slug, deskripsi) VALUES ('Lainnya', 'lainnya', 'Diluar kategori yang sudah ada')";
			$konten_default = "INSERT INTO konten (id_kategori, tanggal, judul, slug, isi, pages) VALUES (1, '$tanggal', 'Lorem Ipsum', 'lorem-ipsum', 'Ridiculus felis a tempus pede. Tellus quis in. Mi libero viverra sem, elit mollis turpis class hymenaeos. Sociis massa phasellus sagittis curae. Class consequat molestie tellus lectus justo posuere, nisi eu. Per, ante lectus. Justo parturient nascetur curae nisi libero ut sed curabitur. Vitae ac ut hymenaeos dignissim eros sit lacus orci dis eget. Nam nisi diam feugiat magna dis nullam a, inceptos nunc diam ut suscipit posuere. Scelerisque nisi, platea. Posuere eni, ve ornare facilisi imperdiet facilisi et ullamcorper nunc, hymenaeos. Purus elit. Vitae eni neque libero netus adipiscing urna. Tempor. Proin velit aliquet donec odio risus, ac ve ve nunc. Id, mattis eleifend viverra pellentesque ultricies. A enim ipsum quis magnis, feugiat et. Ad, eget condimentum sed sociis turpis suscipit parturient semper.', 0)";

			$buat_tabel = $this->db->queri($tabel_utama);
			$buat_tabel = $this->db->queri($tabel_kategori);
			$buat_tabel = $this->db->queri($tabel_konten);
			$buat_tabel = $this->db->queri($tabel_komentar);
			$buat_tabel = $this->db->queri($data_utama);
			$buat_tabel = $this->db->queri($pages_about);
			$buat_tabel = $this->db->queri($kategori_awal);
			$buat_tabel = $this->db->queri($konten_default);
			$data_utama = @$this->db->ambil('utama', '*', "id = 1");
?>