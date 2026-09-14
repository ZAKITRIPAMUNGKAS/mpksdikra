from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm, mm
from reportlab.lib.colors import HexColor
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY, TA_LEFT
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, PageBreak,
    Table, TableStyle, ListFlowable, ListItem, KeepTogether
)

OUTPUT = "Modul_Pengenalan_Dasar_AI.pdf"

# Colors
PRIMARY = HexColor("#1a237e")    # Dark blue
SECONDARY = HexColor("#283593")
ACCENT = HexColor("#3949ab")
LIGHT_BG = HexColor("#e8eaf6")
WHITE = HexColor("#ffffff")
BLACK = HexColor("#000000")
GRAY = HexColor("#616161")

def build_styles():
    styles = getSampleStyleSheet()

    styles.add(ParagraphStyle(
        "CoverTitle", parent=styles["Title"],
        fontSize=28, leading=34, textColor=PRIMARY,
        alignment=TA_CENTER, spaceAfter=10,
        fontName="Helvetica-Bold"
    ))
    styles.add(ParagraphStyle(
        "CoverSub", parent=styles["Normal"],
        fontSize=14, leading=18, textColor=GRAY,
        alignment=TA_CENTER, spaceAfter=6
    ))
    styles.add(ParagraphStyle(
        "ChapterTitle", parent=styles["Heading1"],
        fontSize=20, leading=26, textColor=PRIMARY,
        spaceBefore=0, spaceAfter=14, fontName="Helvetica-Bold"
    ))
    styles.add(ParagraphStyle(
        "SectionTitle", parent=styles["Heading2"],
        fontSize=14, leading=18, textColor=SECONDARY,
        spaceBefore=14, spaceAfter=8, fontName="Helvetica-Bold"
    ))
    styles.add(ParagraphStyle(
        "SubSection", parent=styles["Heading3"],
        fontSize=12, leading=15, textColor=ACCENT,
        spaceBefore=10, spaceAfter=6, fontName="Helvetica-Bold"
    ))
    styles.add(ParagraphStyle(
        "Body", parent=styles["Normal"],
        fontSize=11, leading=16, alignment=TA_JUSTIFY,
        spaceAfter=8
    ))
    styles.add(ParagraphStyle(
        "BulletBody", parent=styles["Normal"],
        fontSize=11, leading=16, alignment=TA_JUSTIFY,
        leftIndent=20, spaceAfter=4
    ))
    styles.add(ParagraphStyle(
        "Quote", parent=styles["Normal"],
        fontSize=11, leading=15, textColor=GRAY,
        leftIndent=30, rightIndent=30, spaceAfter=10,
        fontName="Helvetica-Oblique", alignment=TA_JUSTIFY
    ))
    styles.add(ParagraphStyle(
        "TableHeader", parent=styles["Normal"],
        fontSize=10, leading=13, textColor=WHITE,
        fontName="Helvetica-Bold", alignment=TA_CENTER
    ))
    styles.add(ParagraphStyle(
        "TableCell", parent=styles["Normal"],
        fontSize=10, leading=13, alignment=TA_CENTER
    ))
    styles.add(ParagraphStyle(
        "TableCellLeft", parent=styles["Normal"],
        fontSize=10, leading=13, alignment=TA_LEFT
    ))
    styles.add(ParagraphStyle(
        "Footer", parent=styles["Normal"],
        fontSize=9, textColor=GRAY, alignment=TA_CENTER
    ))
    return styles


def header_footer(canvas, doc):
    canvas.saveState()
    # Header line
    canvas.setStrokeColor(PRIMARY)
    canvas.setLineWidth(1.5)
    canvas.line(2*cm, A4[1] - 1.5*cm, A4[0] - 2*cm, A4[1] - 1.5*cm)
    # Header text
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(GRAY)
    canvas.drawString(2*cm, A4[1] - 1.3*cm, "Modul Pengenalan Dasar Kecerdasan Buatan")
    # Footer
    canvas.setStrokeColor(PRIMARY)
    canvas.line(2*cm, 1.8*cm, A4[0] - 2*cm, 1.8*cm)
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(GRAY)
    canvas.drawCentredString(A4[0]/2, 1.2*cm, f"Halaman {doc.page}")
    canvas.drawString(2*cm, 1.2*cm, "MPKSDI KRA")
    canvas.drawRightString(A4[0] - 2*cm, 1.2*cm, "2026")
    canvas.restoreState()


def first_page(canvas, doc):
    """No header on cover page, just footer."""
    canvas.saveState()
    canvas.setStrokeColor(PRIMARY)
    canvas.line(2*cm, 1.8*cm, A4[0] - 2*cm, 1.8*cm)
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(GRAY)
    canvas.drawCentredString(A4[0]/2, 1.2*cm, "2026")
    canvas.restoreState()


def make_table(headers, rows, styles):
    """Create a styled table."""
    header_cells = [Paragraph(h, styles["TableHeader"]) for h in headers]
    data = [header_cells]
    for row in rows:
        data.append([Paragraph(str(c), styles["TableCellLeft"] if i == 0 else styles["TableCell"])
                      for i, c in enumerate(row)])

    col_widths = [None] * len(headers)
    t = Table(data, colWidths=col_widths, repeatRows=1)
    t.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), PRIMARY),
        ("TEXTCOLOR", (0, 0), (-1, 0), WHITE),
        ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
        ("FONTSIZE", (0, 0), (-1, 0), 10),
        ("ALIGN", (0, 0), (-1, -1), "CENTER"),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [WHITE, LIGHT_BG]),
        ("GRID", (0, 0), (-1, -1), 0.5, HexColor("#bdbdbd")),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
    ]))
    return t


def build_pdf():
    s = build_styles()

    doc = SimpleDocTemplate(
        OUTPUT, pagesize=A4,
        topMargin=2*cm, bottomMargin=2.5*cm,
        leftMargin=2*cm, rightMargin=2*cm
    )
    story = []

    # ======== COVER PAGE ========
    story.append(Spacer(1, 4*cm))
    story.append(Paragraph("MODUL PEMBELAJARAN", s["CoverSub"]))
    story.append(Spacer(1, 0.5*cm))
    story.append(Paragraph("Pengenalan Dasar<br/>Kecerdasan Buatan (AI)", s["CoverTitle"]))
    story.append(Spacer(1, 1*cm))
    story.append(Paragraph("Introduction to Artificial Intelligence", s["CoverSub"]))
    story.append(Spacer(1, 2*cm))

    cover_info = [
        ["Program Studi", ": Informatika / Teknologi Informasi"],
        ["Target Peserta", ": Mahasiswa (Semester 1-3)"],
        ["Jumlah Bab", ": 4 Bab"],
        ["Tahun Ajaran", ": 2026/2027"],
    ]
    cover_t = Table(cover_info, colWidths=[4.5*cm, 10*cm])
    cover_t.setStyle(TableStyle([
        ("FONTNAME", (0, 0), (0, -1), "Helvetica-Bold"),
        ("FONTSIZE", (11, 11), (-1, -1)),
        ("TEXTCOLOR", (0, 0), (-1, -1), GRAY),
        ("TOPPADDING", (0, 0), (-1, -1), 4),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
        ("ALIGN", (0, 0), (0, -1), "RIGHT"),
        ("ALIGN", (1, 0), (1, -1), "LEFT"),
    ]))
    story.append(cover_t)
    story.append(PageBreak())

    # ======== DAFTAR ISI ========
    story.append(Paragraph("DAFTAR ISI", s["ChapterTitle"]))
    story.append(Spacer(1, 0.5*cm))

    toc_items = [
        ("Bab 1", "Pengenalan Kecerdasan Buatan", "3"),
        ("", "1.1  Definisi dan Sejarah AI", "3"),
        ("", "1.2  Jenis-Jenis AI", "4"),
        ("", "1.3  Cabang dan Sub-Cabang AI", "5"),
        ("Bab 2", "Machine Learning: Jantung AI", "6"),
        ("", "2.1  Konsep Dasar Machine Learning", "6"),
        ("", "2.2  Tiga Paradigma Learning", "7"),
        ("", "2.3  Algoritma Populer", "8"),
        ("Bab 3", "Deep Learning dan Aplikasi AI", "9"),
        ("", "3.1  Jaringan Saraf Tiruan (Neural Network)", "9"),
        ("", "3.2  Arsitektur Deep Learning Populer", "10"),
        ("", "3.3  Aplikasi AI di Kehidupan Nyata", "11"),
        ("Bab 4", "Etika dan Masa Depan AI", "12"),
        ("", "4.1  Isu Etika dalam AI", "12"),
        ("", "4.2  Regulasi dan Governance", "13"),
        ("", "4.3  Masa Depan Kecerdasan Buatan", "14"),
    ]
    for bab, title, page in toc_items:
        if bab:
            story.append(Paragraph(
                f"<b>{bab}</b>&nbsp;&nbsp;&nbsp;{title}&nbsp;&nbsp;{'.' * 60}&nbsp;{page}",
                s["Body"]
            ))
        else:
            story.append(Paragraph(
                f"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{title}&nbsp;&nbsp;{'.' * 55}&nbsp;{page}",
                ParagraphStyle("tocsub", parent=s["Body"], leftIndent=1.5*cm, fontSize=10, textColor=GRAY)
            ))

    story.append(PageBreak())

    # ======== BAB 1 ========
    story.append(Paragraph("BAB 1", s["SubSection"]))
    story.append(Paragraph("Pengenalan Kecerdasan Buatan", s["ChapterTitle"]))
    story.append(Spacer(1, 0.3*cm))

    story.append(Paragraph("1.1 &nbsp;Definisi dan Sejarah AI", s["SectionTitle"]))
    story.append(Paragraph(
        "Kecerdasan Buatan atau <i>Artificial Intelligence</i> (AI) adalah bidang ilmu komputer yang "
        "berfokus pada pembuatan sistem mampu melakukan tugas yang biasanya memerlukan kecerdasan "
        "manusia. AI mencakup kemampuan belajar, penalaran, pemecahan masalah, persepsi, dan "
        "pemahaman bahasa.",
        s["Body"]
    ))
    story.append(Paragraph(
        '<i>"Kecerdasan buatan adalah kecerdasan mesin yang ditunjukkan oleh mesin, '
        'berbeda dengan kecerdasan alami yang ditunjukkan oleh manusia dan hewan."</i> '
        "-- John McCarthy (1956), Bapak AI",
        s["Quote"]
    ))

    story.append(Paragraph("Sejarah Singkat Perkembangan AI", s["SubSection"]))
    history_data = [
        ["Tahun", "Peristiwa Penting"],
        ["1950", "Alan Turing mempublikasikan 'Computing Machinery and Intelligence' (Turing Test)"],
        ["1956", "Konferensi Dartmouth: kelahiran resmi bidang AI"],
        ["1966", "ELIZA -- program chatbot pertama oleh Joseph Weizenbaum"],
        ["1997", "IBM Deep Blue mengalahkan juara catur dunia Garry Kasparov"],
        ["2011", "IBM Watson memenangkan Jeopardy!"],
        ["2012", "AlexNet memenangkan ImageNet, memulai era deep learning"],
        ["2016", "AlphaGo mengalahkan juara Go dunia Lee Sedol"],
        ["2022", "ChatGPT dirilis oleh OpenAI, AI memasuki mainstream"],
        ["2023-26", "Era LLM: GPT-4, Claude, Gemini -- AI multimodal dan agen"],
    ]
    story.append(make_table(history_data[0], history_data[1:], s))
    story.append(Spacer(1, 0.5*cm))

    story.append(Paragraph("1.2 &nbsp;Jenis-Jenis AI", s["SectionTitle"]))
    story.append(Paragraph(
        "AI diklasifikasikan berdasarkan dua sumbu: kemampuan dan cara kerja.",
        s["Body"]
    ))

    types_data = [
        ["Jenis", "Deskripsi", "Contoh"],
        ["Berdasarkan Kemampuan", "", ""],
        ["Narrow AI (ANI)", "AI khusus untuk satu tugas tertentu", "Siri, ChatGPT, Google Search"],
        ["General AI (AGI)", "AI dengan kecerdasan setara manusia umum", "Belum ada (masih riset)"],
        ["Super AI (ASI)", "AI yang melebihi kecerdasan manusia", "Hipotetis"],
        ["Berdasarkan Cara Kerja", "", ""],
        ["Reactive AI", "Tidak memiliki memori, hanya responsi", "IBM Deep Blue"],
        ["Limited Memory", "Bisa belajar dari data historis", "ChatGPT, mobil self-driving"],
        ["Theory of Mind", "Memahami emosi dan pikiran manusia", "Riset aktif"],
        ["Self-Aware", "Memiliki kesadaran diri", "Fiksi ilmiah"],
    ]
    story.append(make_table(types_data[0], types_data[1:], s))
    story.append(Spacer(1, 0.5*cm))

    story.append(Paragraph("1.3 &nbsp;Cabang dan Sub-Cabang AI", s["SectionTitle"]))
    story.append(Paragraph(
        "Bidang AI memiliki beberapa cabang utama yang saling berkaitan:",
        s["Body"]
    ))

    branches = [
        ("<b>Machine Learning (ML)</b> -- Sistem belajar dari data tanpa diprogram eksplisit. "
         "Merupakan sub-cabang terluas dari AI."),
        ("<b>Deep Learning</b> -- Subset ML menggunakan jaringan saraf tiruan bertingkat. "
         "Berhasil dalam pengenalan gambar, NLP, dan generasi konten."),
        ("<b>Natural Language Processing (NLP)</b> -- Memahami, menginterpretasi, dan "
         "menghasilkan bahasa manusia. Contoh: terjemahan mesin, chatbot."),
        ("<b>Computer Vision (CV)</b> -- Menganalisis dan memahami konten visual dari gambar "
         "dan video. Contoh: pengenalan wajah, deteksi objek."),
        ("<b>Robotics</b> -- Menggabungkan AI dengan robotika untuk menciptakan sistem "
         "yang bisa berinteraksi dengan dunia fisik."),
        ("<b>Expert Systems</b> -- Sistem yang meniru keputusan ahli dalam domain tertentu "
         "menggunakan basis pengetahuan dan aturan."),
    ]
    for b in branches:
        story.append(Paragraph(f"• &nbsp;{b}", s["BulletBody"]))

    story.append(PageBreak())

    # ======== BAB 2 ========
    story.append(Paragraph("BAB 2", s["SubSection"]))
    story.append(Paragraph("Machine Learning: Jantung AI", s["ChapterTitle"]))
    story.append(Spacer(1, 0.3*cm))

    story.append(Paragraph("2.1 &nbsp;Konsep Dasar Machine Learning", s["SectionTitle"]))
    story.append(Paragraph(
        "Machine Learning adalah teknik di mana komputer belajar pola dari data untuk membuat "
        "keputusan atau prediksi tanpa diprogram secara eksplisit. Konsep intinya adalah "
        "<b>generalisasi</b>: kemampuan model untuk bekerja dengan baik pada data baru "
        "yang belum pernah dilihat sebelumnya.",
        s["Body"]
    ))
    story.append(Paragraph(
        '<i>"Machine Learning adalah ilmu membuat komputer belajar tanpa diprogram secara eksplisit."</i> '
        "-- Tom Mitchell (1997)",
        s["Quote"]
    ))

    story.append(Paragraph("Komponen Penting dalam ML", s["SubSection"]))
    ml_components = [
        ["Komponen", "Penjelasan", "Contoh"],
        ["Dataset", "Kumpulan data untuk training dan evaluasi", "Dataset gambar kucing/anjing"],
        ["Fitur (Features)", "Variabel input yang digunakan model", "Resolusi gambar, warna, ukuran"],
        ["Label", "Output yang diharapkan (supervised)", "Kategori: kucing atau anjing"],
        ["Model", "Fungsi matematika yang memetakan input ke output", "Regresi, pohon keputusan"],
        ["Loss Function", "Ukuran error antara prediksi dan aktual", "MSE, Cross-Entropy"],
        ["Optimizer", "Algoritma untuk memperbaiki model", "Gradient Descent, Adam"],
    ]
    story.append(make_table(ml_components[0], ml_components[1:], s))
    story.append(Spacer(1, 0.5*cm))

    story.append(Paragraph("2.2 &nbsp;Tiga Paradigma Learning", s["SectionTitle"]))

    paradigm_data = [
        ["Paradigma", "Deskripsi", "Kebutuhan Data", "Contoh"],
        ["Supervised Learning", "Belajar dari data berlabel (input-output)", "Data berlabel", "Klasifikasi email spam, prediksi harga rumah"],
        ["Unsupervised Learning", "Menemukan struktur/pola dari data tanpa label", "Data tanpa label", "Clustering pelanggan, reduksi dimensi"],
        ["Reinforcement Learning", "Belajar melalui trial-and-error dengan reward", "Lingkungan simulasi", "Robot berjalan, game AI, autonomous driving"],
    ]
    story.append(make_table(paradigm_data[0], paradigm_data[1:], s))
    story.append(Spacer(1, 0.5*cm))

    story.append(Paragraph("2.3 &nbsp;Algoritma Populer dalam ML", s["SectionTitle"]))

    algo_data = [
        ["Algoritma", "Tipe", "Kasus Penggunaan"],
        ["Linear Regression", "Supervised (Regresi)", "Prediksi numerik: harga, suhu"],
        ["Logistic Regression", "Supervised (Klasifikasi)", "Klasifikasi biner: spam/tidak"],
        ["Decision Tree", "Supervised", "Keputusan berbasis aturan"],
        ["Random Forest", "Supervised", "Ensemble learning, robust terhadap overfitting"],
        ["K-Nearest Neighbors", "Supervised", "Klasifikasi berbasis kemiripan"],
        ["Support Vector Machine", "Supervised", "Klasifikasi dengan margin maksimal"],
        ["K-Means", "Unsupervised", "Clustering data dalam K kelompok"],
        ["Neural Network", "Supervised/Unsupervised", "Pol non-linear kompleks, basis deep learning"],
    ]
    story.append(make_table(algo_data[0], algo_data[1:], s))

    story.append(Spacer(1, 0.5*cm))
    story.append(Paragraph("Alur Kerja Pipeline ML", s["SubSection"]))
    steps = [
        "<b>1. Problem Definition</b> -- Tentukan masalah dan metrik keberhasilan.",
        "<b>2. Data Collection</b> -- Kumpulkan dan pahami dataset.",
        "<b>3. Data Preprocessing</b> -- Bersihkan data, tangani missing values, normalisasi.",
        "<b>4. Feature Engineering</b> -- Pilih atau buat fitur yang relevan.",
        "<b>5. Model Selection</b> -- Pilih algoritma yang sesuai.",
        "<b>6. Training</b> -- Latih model pada data training.",
        "<b>7. Evaluation</b> -- Uji model pada data validasi/test.",
        "<b>8. Deployment</b> -- Terapkan model di produksi.",
    ]
    for st in steps:
        story.append(Paragraph(f"• &nbsp;{st}", s["BulletBody"]))

    story.append(PageBreak())

    # ======== BAB 3 ========
    story.append(Paragraph("BAB 3", s["SubSection"]))
    story.append(Paragraph("Deep Learning dan Aplikasi AI", s["ChapterTitle"]))
    story.append(Spacer(1, 0.3*cm))

    story.append(Paragraph("3.1 &nbsp;Jaringan Saraf Tiruan (Neural Network)", s["SectionTitle"]))
    story.append(Paragraph(
        "Jaringan saraf tiruan (<i>Artificial Neural Network / ANN</i>) terinspirasi dari "
        "struktur otak manusia. Terdiri dari unit-unit yang disebut <b>neuron</b>, "
        "disusun dalam beberapa lapisan (<i>layer</i>):",
        s["Body"]
    ))

    nn_data = [
        ["Layer", "Fungsi", "Contoh"],
        ["Input Layer", "Menerima data mentah dari luar", "Piksel gambar, teks, angka"],
        ["Hidden Layer", "Memproses dan mengekstrak fitur", "Konvolusi, attention, pooling"],
        ["Output Layer", "Menghasilkan prediksi/keputusan", "Probabilitas kelas, nilai regresi"],
    ]
    story.append(make_table(nn_data[0], nn_data[1:], s))
    story.append(Spacer(1, 0.3*cm))

    story.append(Paragraph(
        "Setiap koneksi antar neuron memiliki <b>bobot</b> (<i>weight</i>) yang "
        "disesuaikan selama proses training. Fungsi aktivasi (ReLU, Sigmoid, Softmax) "
        "menentukan output neuron. Proses <b>backpropagation</b> digunakan untuk "
        "memperbarui bobot berdasarkan error.",
        s["Body"]
    ))

    story.append(Paragraph("3.2 &nbsp;Arsitektur Deep Learning Populer", s["SectionTitle"]))

    arch_data = [
        ["Arsitektur", "Keunggulan", "Aplikasi Utama"],
        ["CNN (Convolutional Neural Network)", "Efektif untuk data spasial/grid", "Pengenalan gambar, deteksi objek, CV"],
        ["RNN (Recurrent Neural Network)", "Menangani data sekuensial", "NLP, time series, speech recognition"],
        ["LSTM/GRU", "Menangani dependensi jangka panjang", "Terjemahan mesin, summarization"],
        ["Transformer", "Paralelisasi penuh, self-attention", "LLM (GPT, Claude, Gemini), NLP modern"],
        ["GAN (Generative Adversarial Network)", "Generasi data realistis", "Image generation, style transfer"],
        ["VAE (Variational Autoencoder)", "Generasi dan rekonstruksi data", "Anomaly detection, generasi gambar"],
    ]
    story.append(make_table(arch_data[0], arch_data[1:], s))
    story.append(Spacer(1, 0.5*cm))

    story.append(Paragraph("3.3 &nbsp;Aplikasi AI di Kehidupan Nyata", s["SectionTitle"]))

    app_data = [
        ["Domain", "Aplikasi AI", "Teknologi"],
        ["Kesehatan", "Diagnosis penyakit dari citra medis, drug discovery", "CNN, NLP"],
        ["Transportasi", "Mobil self-driving, optimasi rute", "Computer Vision, RL"],
        ["Keuangan", "Deteksi penipuan, trading algoritmik", "ML, Deep Learning"],
        ["Edukasi", "Sistem tutor adaptif, penilaian otomatis", "NLP, ML"],
        ["Hiburan", "Rekomendasi konten, generasi konten kreatif", "Transformer, GAN"],
        ["Industri", "Prediktif maintenance, optimasi produksi", "IoT + ML"],
        ["Pertanian", "Monitoring tanaman, prediksi hasil panen", "CV, time series"],
    ]
    story.append(make_table(app_data[0], app_data[1:], s))

    story.append(PageBreak())

    # ======== BAB 4 ========
    story.append(Paragraph("BAB 4", s["SubSection"]))
    story.append(Paragraph("Etika dan Masa Depan AI", s["ChapterTitle"]))
    story.append(Spacer(1, 0.3*cm))

    story.append(Paragraph("4.1 &nbsp;Isu Etika dalam AI", s["SectionTitle"]))
    story.append(Paragraph(
        "Perkembangan AI membawa berbagai tantangan etika yang perlu diperhatikan:",
        s["Body"]
    ))

    ethics = [
        ("<b>Bias dan Fairness</b> -- Model AI dapat memperkuat bias yang ada dalam data "
         "training, menyebabkan diskriminasi terhadap kelompok tertentu."),
        ("<b>Transparensi (Explainability)</b> -- Banyak model AI (terutama deep learning) "
         "bersifat 'black box', sulit dijelaskan mengapa keputusan tertentu diambil."),
        ("<b>Privasi Data</b> -- AI membutuhkan data dalam jumlah besar, yang berpotensi "
         "melanggar privasi individu."),
        ("<b>Keamanan dan Penyalahgunaan</b> -- Deepfake, autonomous weapons, dan "
         "disinformasi yang digerakkan oleh AI."),
        ("<b>Dampak Sosial</b> -- Otomasi pekerjaan, kesenjangan digital, dan "
         "konsentrasi kekuasaan pada segelintir perusahaan."),
    ]
    for e in ethics:
        story.append(Paragraph(f"• &nbsp;{e}", s["BulletBody"]))

    story.append(Paragraph("4.2 &nbsp;Regulasi dan Governance", s["SectionTitle"]))
    story.append(Paragraph(
        "Beberapa inisiatif regulasi dan kerangka kerja AI yang berkembang:",
        s["Body"]
    ))

    reg_data = [
        ["Inisiatif", "Asal", "Fokus Utama"],
        ["EU AI Act", "Uni Eropa", "Regulasi berbasis risiko, transparensi, audit"],
        ["NIST AI RMF", "Amerika Serikat", "Framework manajemen risiko AI"],
        ["OECD AI Principles", "OECD", "Prinsip AI yang inklusif dan berkelanjutan"],
        ["Indonesia AI Strategy", "Indonesia", "Strategi nasional AI 2020-2045"],
        ["UNESCO AI Ethics", "PBB", "Rekomendasi global tentang etika AI"],
    ]
    story.append(make_table(reg_data[0], reg_data[1:], s))
    story.append(Spacer(1, 0.5*cm))

    story.append(Paragraph("4.3 &nbsp;Masa Depan Kecerdasan Buatan", s["SectionTitle"]))
    story.append(Paragraph(
        "Beberapa tren utama yang membentuk masa depan AI:",
        s["Body"]
    ))

    trends = [
        ("<b>Artificial General Intelligence (AGI)</b> -- Pencarian AI yang memiliki "
         "kemampuan kognitif setara manusia secara umum. Masih menjadi tujuan jangka panjang."),
        ("<b>Multimodal AI</b> -- Model yang memproses dan menghasilkan berbagai jenis "
         "data (teks, gambar, audio, video) secara bersamaan."),
        ("<b>AI Agents</b> -- Sistem AI yang dapat bertindak secara otonom, menggunakan "
         "tool, dan menjalankan tugas kompleks berlangkah."),
        ("<b>Edge AI</b> -- Menjalankan model AI langsung di perangkat lokal "
         "(HP, IoT) tanpa bergantung pada cloud."),
        ("<b>Responsible AI</b> -- Fokus pada AI yang transparan, adil, "
         "dapat dijelaskan, dan selaras dengan nilai kemanusiaan."),
        ("<b>Human-AI Collaboration</b> -- AI bukan pengganti manusia, "
         "melainkan kolaborator yang meningkatkan kemampuan manusia."),
    ]
    for t in trends:
        story.append(Paragraph(f"• &nbsp;{t}", s["BulletBody"]))

    story.append(Spacer(1, 1*cm))
    story.append(Paragraph("Daftar Pustaka", s["SectionTitle"]))
    refs = [
        "Russell, S. & Norvig, P. (2021). <i>Artificial Intelligence: A Modern Approach</i>. 4th ed. Pearson.",
        "Goodfellow, I., Bengio, Y. & Courville, A. (2016). <i>Deep Learning</i>. MIT Press.",
        "Mitchell, T. (1997). <i>Machine Learning</i>. McGraw Hill.",
        "McCarthy, J. (2007). 'What is Artificial Intelligence?'. Stanford University.",
        "European Commission. (2024). <i>EU Artificial Intelligence Act</i>.",
        "Kementerian Kominfo RI. (2020). <i>Strategi Nasional Kecerdasan Buatan 2020-2045</i>.",
    ]
    for i, r in enumerate(refs, 1):
        story.append(Paragraph(f"[{i}] &nbsp;{r}", ParagraphStyle(
            "ref", parent=s["Body"], fontSize=10, leftIndent=1*cm, firstLineIndent=-1*cm
        )))

    # Build
    doc.build(story, onFirstPage=first_page, onLaterPages=header_footer)
    print(f"PDF generated: {OUTPUT}")


if __name__ == "__main__":
    build_pdf()
