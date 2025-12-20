<?php
require_once __DIR__.'/fpdf/fpdf.php';

if(class_exists('FPDF')){
    echo "FPDF BERHASIL LOAD ✅";
}else{
    echo "FPDF GAGAL ❌";
}
