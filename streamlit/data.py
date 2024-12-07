import streamlit as st
import pandas as pd
import numpy as np

# Header
st.title("Data Perpustakaan")


# Cara mengambil dataset dari Github
buku = pd.read_csv(
    "https://raw.githubusercontent.com/Mellias/sementara/refs/heads/main/bukuu.csv", 
    delimiter=';')
peminjaman = pd.read_csv(
    "https://raw.githubusercontent.com/Mellias/sementara/refs/heads/main/peminjamann.csv", 
    delimiter=';')

# Mengatasi format id anggota dan klasifikasi
peminjaman['ID Anggota'] = peminjaman['ID Anggota'].astype(str)
buku['KLASIFIKASI'] = buku['KLASIFIKASI'].fillna(0).astype(int)


# CARA MENAMPILKAN DATA

# Dataframe
st.text("ini dataframe")
st.dataframe(buku)
st.dataframe(peminjaman)

# Table
st.text("Data Tabel")
st.table(peminjaman)
st.table(buku)
