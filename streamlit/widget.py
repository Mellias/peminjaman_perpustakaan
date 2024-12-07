import streamlit as st
import pandas as pd
import plotly.express as px

# MEMBUAT WIDGET
# 1. Create Button
if (st.button("Kirim")):
    st.text("Data berhasil dibuat")

# 2. Create checkbox
if st.checkbox("Klik untuk menampilkan keterangan"):
    st.text("Berikut adalah keterangan hasil analisis yang telah dilakukan")

# 3. Create radio button
status = st.radio("Pilih Dashboard yang akan dilihat:",('Tipe Keanggotaan','Status Peminjaman','Bulan','Klasifikasi Buku yang dipinjam'))
st.write("Berikut adalah hasil dashboard yang Anda pilih:",status)

# 4. Create Selection Box
domain = st.selectbox("Pilih Dashboard yang akan dilihat: (Pilih satu saja)",
                      ['Tipe Keanggotaan','Status Peminjaman','Bulan','Klasifikasi Buku yang dipinjam'])

st.write("Berikut adalah hasil dashboard yang Anda pilih:",domain)

# 5. Create Multiselect
domain = st.multiselect("Anda juga dapat melihat dahboard yang tampil lebih dari satu",
                        ['Tipe Keanggotaan','Status Peminjaman','Bulan','Klasifikasi Buku yang dipinjam'])
st.write("Jumlah hasil dashboard yang muncul adalah sebanyak", len(domain), 'diagram')

# 6. Create slider
level = st.slider("Pilih level",1,10)

st.text('Dipilih:{}'.format(level))

# 7. Create select-slider
hari = st.select_slider('Pilih nama-nama hari', 
                        options=['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'])

st.write('Hari kesukaan kamu:',hari)

# 8. Create Input Text
test1 = st.text_input("Silahkan masukkan teks","")

st.write("Kamu sudah berhasil menginput:",test1)

# 9. Create Input Number
test2 = st.number_input("Silahkan masukkan angka",1,10)

st.write("Kamu sudah berhasil menginput angka",test2)

# 10. Create Text Area
test3 = st.text_area("Silahkan masukan text (klik ctrl+enter)","")

st.write("Kamu sudah berhasil menginput teks area",test3)

# 11. Create Input Date
st.date_input("Silahkan input date")

# 12. Create Time Input
st.time_input("silahkan masukkan waktu")

# 13. Create File Uploader
st.file_uploader("Upload File",type=["csv","txt"])

# 15. Create color picker
color = st.color_picker('Pick a Color','#00f900')
st.write('The current color is',color)