import streamlit as st
import pandas as pd
import plotly.express as px
from PIL import Image

# Header
st.title("Selamat Datang di Dashboard Data Perpustakaan")

# Masukkan gambar logo
gambar1 = Image.open('images/Logo UMRAH.png')

st.image(gambar1)

# File Uploader
uploaded_file = st.file_uploader("Upload File", type=["csv", "txt"])
if uploaded_file:
    st.write("File berhasil diunggah!")
    uploaded_data = pd.read_csv(uploaded_file)
    st.write(uploaded_data.head())

# Cara mengambil dataset dari Github
buku = pd.read_csv(
    "https://raw.githubusercontent.com/Mellias/sementara/refs/heads/main/bukuu.csv", 
    delimiter=';'
)
peminjaman = pd.read_csv(
    "https://raw.githubusercontent.com/Mellias/sementara/refs/heads/main/peminjamann.csv", 
    delimiter=';'
)

# Data Preparation
peminjaman['ID Anggota'] = peminjaman['ID Anggota'].astype(str)
buku['KLASIFIKASI'] = buku['KLASIFIKASI'].fillna(0).astype(int)

# 1. Grafik Tipe Keanggotaan
peminjaman_agg = peminjaman.groupby('Tipe Keanggotaan').size().reset_index(name='Total')
peminjaman_chart = px.bar(
    data_frame=peminjaman_agg,
    x='Tipe Keanggotaan',
    y='Total',
    title="Total Peminjaman Berdasarkan Tipe Keanggotaan",
    labels={'Tipe Keanggotaan': 'Tipe Keanggotaan', 'Total': 'Jumlah Peminjaman'}
)

# 2. Grafik Status Peminjaman
status_agg = peminjaman.groupby('Status peminjaman').size().reset_index(name='Total')
status_chart = px.pie(
    data_frame=status_agg,
    names='Status peminjaman',
    values='Total',
    title="Distribusi Status Peminjaman"
)

# 3. Grafik Klasifikasi Buku yang Dipinjam
klasifikasi_agg = peminjaman.groupby('Nama Klasifikasi').size().reset_index(name='Total')
klasifikasi_chart = px.bar(
    data_frame=klasifikasi_agg,
    x='Nama Klasifikasi',
    y='Total',
    title="Jumlah Buku Dipinjam Berdasarkan Klasifikasi",
    labels={'Nama Klasifikasi': 'Klasifikasi', 'Total': 'Jumlah Peminjaman'}
)

# 4. Grafik Total Klasifikasi Buku Berdasarkan Jumlah Buku
total_klasifikasi_agg = buku.groupby('NAMA KLASIFIKASI').size().reset_index(name='Jumlah Buku')
total_klasifikasi_chart = px.bar(
    data_frame=total_klasifikasi_agg,
    x='NAMA KLASIFIKASI',
    y='Jumlah Buku',
    title="Total Buku Berdasarkan Klasifikasi",
    labels={'NAMA KLASIFIKASI': 'Klasifikasi', 'Jumlah Buku': 'Jumlah Buku'}
)

# 5. Grafik Bulan Peminjaman Buku
bulan_agg = peminjaman.groupby('Bulan Peminjaman').size().reset_index(name='Total')
bulan_chart = px.line(
    data_frame=bulan_agg,
    x='Bulan Peminjaman',
    y='Total',
    title="Jumlah Peminjaman Buku Per Bulan",
    labels={'Bulan Peminjaman': 'Bulan', 'Total': 'Jumlah Peminjaman'}
)

# Layout
st.header("Infografis Data Perpustakaan")

# Widget Selection Box
domain = st.selectbox("Pilih Dashboard yang akan dilihat:",
                      ['Tipe Keanggotaan', 'Status Peminjaman', 'Bulan', 'Klasifikasi Buku yang dipinjam', 'Klasifikasi Buku Secara Keseluruhan'])

# Conditional Display of Selected Dashboard
if domain == 'Tipe Keanggotaan':
    st.subheader("Total Peminjaman Berdasarkan Tipe Keanggotaan")
    st.plotly_chart(peminjaman_chart, use_container_width=True)

elif domain == 'Status Peminjaman':
    st.subheader("Distribusi Status Peminjaman")
    st.plotly_chart(status_chart, use_container_width=True)

elif domain == 'Bulan':
    st.subheader("Jumlah Peminjaman Buku Per Bulan")
    st.plotly_chart(bulan_chart, use_container_width=True)

elif domain == 'Klasifikasi Buku yang dipinjam':
    st.subheader("Jumlah Buku Dipinjam Berdasarkan Klasifikasi")
    st.plotly_chart(klasifikasi_chart, use_container_width=True)

elif domain == 'Klasifikasi Buku Secara Keseluruhan':
    st.subheader("Jumlah Total Buku")
    st.plotly_chart(total_klasifikasi_chart, use_container_width=True)
