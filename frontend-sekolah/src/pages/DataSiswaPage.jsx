import { useState, useMemo, useEffect } from "react";

export default function DataSiswaPage() {
  const [search, setSearch] = useState("");
  const [kelasFilter, setKelasFilter] = useState("");
  const [perPage, setPerPage] = useState(5);
  const [page, setPage] = useState(1);

  // Contoh data — ganti / fetch dari API nanti
  const siswaData = [
    { nama: "ABELLE PUTRI", nisn: "123456", username: "abelle", kelas: "IA" },
    { nama: "CARMEN CALLISTA", nisn: "145678", username: "carmen", kelas: "IA" },
    { nama: "DANIRA SABILA", nisn: "156879", username: "danira", kelas: "IB" },
    { nama: "EMANUEL AETALLA", nisn: "198270", username: "emanuel", kelas: "IB" },
    { nama: "CARMEN CALLISTA", nisn: "145667", username: "carmen", kelas: "IB" },
    { nama: "GABRIEL SAPUTRA", nisn: "139852", username: "gabriel", kelas: "IC" },
    { nama: "HANA PUTRI", nisn: "111222", username: "hana", kelas: "IC" },
    { nama: "INTAN S", nisn: "333444", username: "intan", kelas: "IA" },
    { nama: "JOKO PRIYONO", nisn: "555666", username: "joko", kelas: "IB" },
  ];

  // Buat daftar kelas unik dari data 
  const kelasOptions = useMemo(() => {
    const setK = new Set(siswaData.map((s) => s.kelas));
    return Array.from(setK).sort();
  }, [siswaData]);

  useEffect(() => {
    setPage(1);
  }, [search, kelasFilter, perPage]);

  // Filter data berdasarkan search (nama) dan kelas
  const filtered = useMemo(() => {
    const s = search.trim().toLowerCase();
    return siswaData.filter((item) => {
      const matchNama = s === "" ? true : item.nama.toLowerCase().includes(s);
      const matchKelas = kelasFilter === "" ? true : item.kelas === kelasFilter;
      return matchNama && matchKelas;
    });
  }, [siswaData, search, kelasFilter]);

  const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
  const start = (page - 1) * perPage;
  const shown = filtered.slice(start, start + perPage);

  return (
    <div className="card p-4 shadow-sm rounded-4">
      <div className="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h5 className="fw-bold mb-1">Daftar Siswa</h5>
        </div>

        {/* Pilihan per page */}
        <div className="d-flex gap-2 align-items-center">
          <label className="mb-0 me-1 small">Tampilkan</label>
          <select
            className="form-select form-select-sm"
            style={{ width: "110px" }}
            value={perPage}
            onChange={(e) => setPerPage(parseInt(e.target.value))}
          >
            <option value={5}>5 / halaman</option>
            <option value={10}>10 / halaman</option>
            <option value={20}>20 / halaman</option>
          </select>
        </div>
      </div>

      {/* Bar filter: search nama + dropdown kelas */}
      <div className="row g-2 mb-3">
        <div className="col-md-6">
          <input
            type="text"
            className="form-control"
            placeholder="Cari nama siswa"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>

        <div className="col-md-3">
          <select
            className="form-select"
            value={kelasFilter}
            onChange={(e) => setKelasFilter(e.target.value)}
          >
            <option value="">Pilih Kelas</option>
            {kelasOptions.map((k) => (
              <option key={k} value={k}>
                {k}
              </option>
            ))}
          </select>
        </div>

        <div className="col-md-3 d-flex justify-content-end">
          <button
            className="btn btn-outline-secondary"
            onClick={() => {
              setSearch("");
              setKelasFilter("");
            }}
          >
            Reset Filter
          </button>
        </div>
      </div>

      {/* Tabel */}
      <div className="table-responsive">
        <table className="table table-bordered align-middle mb-0">
          <thead className="table-light">
            <tr>
              <th style={{ width: 60 }}>No</th>
              <th>Nama Lengkap</th>
              <th style={{ width: 140 }}>NISN</th>
              <th style={{ width: 160 }}>Username</th>
              <th style={{ width: 100 }}>Kelas</th>
              <th style={{ width: 140 }}>Aksi</th>
            </tr>
          </thead>
          <tbody>
            {shown.length > 0 ? (
              shown.map((s, idx) => (
                <tr key={s.nisn + idx}>
                  <td>{start + idx + 1}</td>
                  <td>{s.nama}</td>
                  <td>{s.nisn}</td>
                  <td>{s.username}</td>
                  <td>{s.kelas}</td>
                  <td>
                    <button className="btn btn-sm btn-primary me-2">Detail</button>
                    <button className="btn btn-sm btn-danger">Hapus</button>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={6} className="text-center text-muted">
                  Tidak ada data ditemukan.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      <div className="d-flex justify-content-between align-items-center mt-3">
        <div className="text-muted small">
          Menampilkan {filtered.length === 0 ? 0 : start + 1} -{" "}
          {Math.min(start + shown.length, filtered.length)} dari {filtered.length} data
        </div>

        <div className="btn-group">
          <button
            className="btn btn-sm btn-outline-primary"
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            disabled={page === 1}
          >
            Prev
          </button>

          {/* simple pagination: show beberapa nomor */}
          {Array.from({ length: totalPages }, (_, i) => i + 1).map((pNum) => {
            // show max 7 tombol: current -3 .. current +3
            if (totalPages > 7) {
              const min = Math.max(1, page - 3);
              const max = Math.min(totalPages, page + 3);
              if (pNum < min || pNum > max) return null;
            }
            return (
              <button
                key={pNum}
                className={`btn btn-sm ${page === pNum ? "btn-primary" : "btn-outline-primary"}`}
                onClick={() => setPage(pNum)}
              >
                {pNum}
              </button>
            );
          })}

          <button
            className="btn btn-sm btn-outline-primary"
            onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
            disabled={page === totalPages}
          >
            Next
          </button>
        </div>
      </div>
    </div>
  );
}