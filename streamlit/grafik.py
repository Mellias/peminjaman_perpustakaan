import streamlit as st
import pandas as pd
import plotly.express as px

# Header
st.title("Dashboard Data Perpustakaan")

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

# 1. Grafik Tipe Peminjaman
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

# Row 1: Status Peminjaman dan Klasifikasi Buku yang Dipinjam
col1, col2 = st.columns(2)
with col1:
    st.plotly_chart(status_chart, use_container_width=True)
with col2:
    st.plotly_chart(klasifikasi_chart, use_container_width=True)

# Row 2: Total Klasifikasi Buku dan Bulan Peminjaman
col3, col4 = st.columns(2)
with col3:
    st.plotly_chart(total_klasifikasi_chart, use_container_width=True)
with col4:
    st.plotly_chart(bulan_chart, use_container_width=True)

# Row 3: Grafik Tipe Peminjaman
st.subheader("Grafik Tambahan")
st.plotly_chart(peminjaman_chart, use_container_width=True)
