import { useState } from "react";

export default function EMateriPage() {
    const [formData, setFormData] = useState({
        judul: "",
        kelas: "",
        kelompok: "",
        mapel: "",
        tglAwal: "",
        tglAkhir: ""
    });

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    return (
        <div className="p-4">
            <h4 className="fw-bold">Daftar E-Materi</h4>

            {/* Filter / Form Input */}
            <div className="bg-white p-3 rounded shadow-sm mt-3">
                <div className="row g-3">
                    <div className="col-md-4">
                        <label className="form-label">Judul E-Materi</label>
                        <input
                            type="text"
                            className="form-control"
                            name="judul"
                            value={formData.judul}
                            onChange={handleChange}
                            placeholder="Masukkan judul"
                        />
                    </div>

                    <div className="col-md-4">
                        <label className="form-label">Kelas</label>
                        <select
                            className="form-select"
                            name="kelas"
                            value={formData.kelas}
                            onChange={handleChange}
                        >
                            <option value="">Pilih Kelas</option>
                            <option value="5A">5A</option>
                            <option value="5B">5B</option>
                        </select>
                    </div>

                    <div className="col-md-4">
                        <label className="form-label">Kelompok</label>
                        <select
                            className="form-select"
                            name="kelompok"
                            value={formData.kelompok}
                            onChange={handleChange}
                        >
                            <option value="">Pilih Kelompok</option>
                            <option value="Kelompok A">Kelompok A</option>
                            <option value="Kelompok B">Kelompok B</option>
                        </select>
                    </div>

                    <div className="col-md-4">
                        <label className="form-label">Mata Pelajaran</label>
                        <select
                            className="form-select"
                            name="mapel"
                            value={formData.mapel}
                            onChange={handleChange}
                        >
                            <option value="">Pilih Mata Pelajaran</option>
                            <option value="Agama Islam">Agama Islam</option>
                        </select>
                    </div>

                    <div className="col-md-4">
                        <label className="form-label">Tanggal Awal</label>
                        <input
                            type="date"
                            className="form-control"
                            name="tglAwal"
                            value={formData.tglAwal}
                            onChange={handleChange}
                        />
                    </div>

                    <div className="col-md-4">
                        <label className="form-label">Tanggal Akhir</label>
                        <input
                            type="date"
                            className="form-control"
                            name="tglAkhir"
                            value={formData.tglAkhir}
                            onChange={handleChange}
                        />
                    </div>
                </div>

                {/* Tombol Aksi Filter */}
                <div className="mt-3 d-flex gap-2">
                    <button className="btn btn-primary">Cari</button>
                    <button className="btn btn-outline-secondary">Reset</button>

                    {/* Tambah Materi */}
                    <button className="btn btn-success ms-auto">+ Tambah Materi</button>
                </div>
            </div>

            {/* Data Tabel */}
            <div className="bg-white p-3 rounded shadow-sm mt-4">
                <table className="table table-bordered text-center align-middle">
                    <thead className="table-secondary">
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Tanggal Dibuat</th>
                            <th>Dibuat Oleh</th>
                            <th>File</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Pembelajaran Tahfizh</td>
                            <td>Agama Islam</td>
                            <td>Cecop Sudirman</td>
                            <td>5A</td>
                            <td>11-11-2025</td>
                            <td>Cecop Sudirman</td>
                            <td className="text-primary" style={{ cursor: "pointer" }}>
                                Download
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    );
}
