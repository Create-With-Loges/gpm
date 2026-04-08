import os
from fpdf import FPDF

# Configuration
PROJECT_DIR = r"c:/xampp/htdocs/gpm"
OUTPUT_FILE = r"c:/xampp/htdocs/gpm/project_code.pdf"
EXTENSIONS = ['.php', '.sql']

class PDF(FPDF):
    def header(self):
        self.set_font('Arial', 'B', 12)
        self.cell(0, 10, 'Out Pass Monitoring System - Codebase', 0, 1, 'C')
        self.ln(5)

    def footer(self):
        self.set_y(-15)
        self.set_font('Arial', 'I', 8)
        self.cell(0, 10, f'Page {self.page_no()}', 0, 0, 'C')

    def chapter_title(self, title):
        self.set_font('Arial', 'B', 12)
        self.set_fill_color(200, 220, 255)
        self.cell(0, 6, title, 0, 1, 'L', 1)
        self.ln(4)

    def chapter_body(self, body):
        self.set_font('Courier', '', 10) # Monospace for code
        self.multi_cell(0, 5, body)
        self.ln()

def get_file_structure(root_dir):
    structure = "Project Folder Structure:\n"
    structure += "=========================\n\n"
    root_name = os.path.basename(root_dir)
    structure += f"{root_name}/\n"
    
    for root, dirs, files in os.walk(root_dir):
        level = root.replace(root_dir, '').count(os.sep)
        indent = ' ' * 4 * (level + 1)
        subindent = ' ' * 4 * (level + 2)
        if root != root_dir:
            structure += f"{indent}{os.path.basename(root)}/\n"
        
        for f in files:
            if any(f.endswith(ext) for ext in EXTENSIONS) or f == 'style.css': # Optional CSS inclusion in tree
                 structure += f"{subindent}{f}\n"
    return structure

pdf = PDF()
pdf.add_page()

# 1. Folder Structure Page
pdf.set_font('Courier', 'B', 12)
pdf.cell(0, 10, 'Folder Structure', 0, 1)
pdf.ln(5)
pdf.set_font('Courier', '', 10)
structure = get_file_structure(PROJECT_DIR)
pdf.multi_cell(0, 5, structure)

# 2. File Contents
for root, dirs, files in os.walk(PROJECT_DIR):
    for filename in files:
        if any(filename.endswith(ext) for ext in EXTENSIONS):
            filepath = os.path.join(root, filename)
            
            # Read logic with encoding handling
            try:
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
            except UnicodeDecodeError:
                try:
                    with open(filepath, 'r', encoding='latin-1') as f:
                        content = f.read()
                except:
                    content = "[Error reading file]"

            pdf.add_page()
            rel_path = os.path.relpath(filepath, PROJECT_DIR)
            pdf.chapter_title(rel_path)
            pdf.chapter_body(content)

try:
    pdf.output(OUTPUT_FILE)
    print(f"PDF successfully generated at: {OUTPUT_FILE}")
except Exception as e:
    print(f"Error generating PDF: {e}")
