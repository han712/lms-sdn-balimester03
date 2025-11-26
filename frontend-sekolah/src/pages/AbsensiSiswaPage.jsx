import { useState, useEffect } from "react";

export default function AbsensiSiswaPage() {
  const [search, setSearch] = useState("");
  const [kelas, setKelas] = useState("");
  const [tanggal, setTanggal] = useState(() =>
    new Date().toISOString().slice(0, 10)
  );
  const [absensi, setAbsensi] = useState({});

  const siswaData = [
    { nama: "ABELLE PUTRI", nisn: "123456", wali: "Budi", kelas: "IA" },
    { nama: "CARMEN CALLISTA", nisn: "145678", wali: "Sari", kelas: "IA" },
    { nama: "DANIRA SABILA", nisn: "156879", wali: "Tono", kelas: "IB" },
    { nama: "EMANUEL AETALLA", nisn: "198270", wali: "Maya", kelas: "IB" },
  ];

  // Load dari localStorage
  useEffect(() => {
    const saved = JSON.parse(localStorage.getItem("absensi")) || {};
    setAbsensi(saved);
  }, []);

  // Save ke localStorage setiap ada perubahan
  const saveAbsensi = (data) => {
    localStorage.setItem("absensi", JSON.stringify(data));
  };

  const filtered = siswaData.filter(
    (item) =>
      item.nama.toLowerCase().includes(search.toLowerCase()) &&
      (kelas === "" || item.kelas === kelas)
  );

  const setStatus = (siswa, status) => {
    const key = `${siswa.nisn}-${tanggal}`;
    const updated = {
      ...absensi,
      [key]: {
        ...siswa,
        status,
        tanggal,
      },
    };

    setAbsensi(updated);
    saveAbsensi(updated);
  };

  const getStatus = (siswa) => {
    const key = `${siswa.nisn}-${tanggal}`;
    return absensi[key]?.status || null;
  };

  return (
    <div className="card p-4 shadow-sm rounded-4">
      <h5 className="fw-bold mb-4">Absensi Siswa</h5>

      {/* FILTER */}
      <div className="row mb-4">
        <div className="col-md-4">
          <input
            type="text"
            placeholder="Cari nama siswa..."
            className="form-control"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>

        <div className="col-md-3">
          <select
            className="form-select"
            value={kelas}
            onChange={(e) => setKelas(e.target.value)}
          >
            <option value="">Pilih Kelas</option>
            <option value="IA">IA</option>
            <option value="IB">IB</option>
            <option value="IC">IC</option>
          </select>
        </div>

        <div className="col-md-3">
          <input
            type="date"
            className="form-control"
            value={tanggal}
            onChange={(e) => setTanggal(e.target.value)}
          />
        </div>
      </div>

      {/* TABLE */}
      <table className="table table-bordered">
        <thead className="table-light">
          <tr>
            <th>No</th>
            <th>Nama Lengkap</th>
            <th>NIS</th>
            <th>Nama Wali</th>
            <th>Status</th>
            <th style={{ width: 250 }}>Action</th>
          </tr>
        </thead>
        <tbody>
          {filtered.length > 0 ? (
            filtered.map((item, i) => {
              const status = getStatus(item);

              return (
                <tr key={i}>
                  <td>{i + 1}</td>
                  <td>{item.nama}</td>
                  <td>{item.nisn}</td>
                  <td>{item.wali}</td>
                  <td>
                    {status ? (
                      <span className="badge bg-primary">{status}</span>
                    ) : (
                      "-"
                    )}
                  </td>
                  <td>
                    <button
                      className="btn btn-success btn-sm me-2"
                      disabled={status !== null}
                      onClick={() => setStatus(item, "Masuk")}
                    >
                      Masuk
                    </button>
                    <button
                      className="btn btn-warning btn-sm me-2"
                      disabled={status !== null}
                      onClick={() => setStatus(item, "Izin")}
                    >
                      Izin
                    </button>
                    <button
                      className="btn btn-danger btn-sm"
                      disabled={status !== null}
                      onClick={() => setStatus(item, "Alfa")}
                    >
                      Alfa
                    </button>
                  </td>
                </tr>
              );
            })
          ) : (
            <tr>
              <td colSpan="6" className="text-center text-muted">
                Tidak ada data ditemukan
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}