import React, { useState } from "react";

const MataPelajaranPage = () => {
  const [selectedClass, setSelectedClass] = useState("");

  return (
    <div style={{ padding: "20px 40px" }}>
      <h2 className="fw-bold mb-4" style={{ fontSize: "24px" }}>
        Mata Pelajaran
      </h2>

      <div
        className="shadow bg-white rounded p-4"
        style={{ padding: "30px", minHeight: "600px" }}
      >
        {/* Dropdown */}
        <div className="d-flex align-items-center gap-2 mb-3">
          <label
            className="fw-semibold"
            style={{ fontSize: "14px", marginRight: "10px" }}
          >
          </label>

          <select
            className="form-select form-select-sm"
            value={selectedClass}
            onChange={(e) => setSelectedClass(e.target.value)}
            style={{
              width: "150px",
              fontSize: "14px",
              border: "1px solid #888",
            }}
          >
            <option value="">Pilih Kelas</option>
            <option value="3A">3A</option>
            <option value="3B">3B</option>
          </select>
        </div>

        {/* Table Container Center */}
        <div className="d-flex justify-content-center mt-3">
          <table
            className="table table-bordered text-center"
            style={{
              width: "900px",
              border: "1px solid black",
              fontSize: "13px",
            }}
          >
            <thead style={{ fontWeight: "600" }}>
              <tr>
                <th>JAM KE</th>
                <th>WAKTU</th>
                <th>SENIN</th>
                <th>SELASA</th>
                <th>RABU</th>
                <th>KAMIS</th>
                <th>JUMAT</th>
              </tr>
            </thead>

            <tbody>
              {[
                ["1", "12.30 - 13.05", "PEMBIASAAN", "PEMBIASAAN", "PEMBIASAAN", "PEMBIASAAN", ""],
                ["2", "13.05 - 13.40", "PJOK", "PAI&BP", "PEND.PANCASILA", "SENI RUPA", ""],
                ["3", "13.40 - 14.15", "PJOK", "PAI&BP", "PEND.PANCASILA", "SENI RUPA", "PEMBIASAAN"],
                ["4", "14.15 - 14.50", "PJOK", "PAI&BP", "MATEMATIKA", "SENI RUPA", "PLBJ"],
                ["5", "14.50 - 15.10", "ISTIRAHAT", "ISTIRAHAT", "ISTIRAHAT", "ISTIRAHAT", "PLBJ"],
                ["6", "15.10 - 15.45", "MATEMATIKA", "BAHASA INDONESIA", "MATEMATIKA", "BAHASA INDONESIA", "ISTIRAHAT"],
                ["7", "15.45 - 16.20", "MATEMATIKA", "BAHASA INDONESIA", "BAHASA INDONESIA", "BAHASA INDONESIA", "PEND.PANCASILA"],
                ["8", "16.20 - 16.55", "", "BAHASA INDONESIA", "BAHASA INDONESIA", "BAHASA INDONESIA", "PEND.PANCASILA"],
                ["9", "16.55 - 17.30", "", "", "BAHASA INDONESIA", "", ""],
              ].map((row, idx) => (
                <tr key={idx}>
                  {row.map((col, i) => (
                    <td key={i} style={{ border: "1px solid black" }}>
                      {col}
                    </td>
                  ))}
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <p
          className="text-muted mt-2"
          style={{ fontSize: "11px", marginLeft: "20px" }}
        >
          *Jadwal bisa berubah sewaktu-waktu
        </p>
      </div>
    </div>
  );
};

export default MataPelajaranPage;