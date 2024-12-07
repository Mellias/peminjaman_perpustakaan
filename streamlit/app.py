# import module
import streamlit as st

# title
st.title("Perpustakaan UMRAH Dompak")

# Header
st.header("Data Peminjaman")
st.header("Data Koleksi Buku")

# Subheader
st.subheader("Berikut adalah data peminjaman buku")
st.subheader("Berikut adalah data koleksi buku")

# Text
st.text("Lorem ipsum dolor sit amet")

# Markdown
st.markdown("# Markdown1")
st.markdown("## Markdown1")
st.markdown("### Markdown1")
st.markdown("#### Markdown1")

# Markdown Multibaris
st.markdown("""
# test1
            
## test2

### test3          
""",True)

# Code Block
code = '''def hello():
    print("Hello, Streamlit!")'''
st.code(code, language='python')

#LaTex
st.latex(r'''
         a + ar + a r^2 + a r^3 + \cdots + a r^{n-1} = 
         \sum_{k=0}^{n-1} ar^k = 
         a \left(\frac{1-r^{n}}{1-r}\right)
        ''')