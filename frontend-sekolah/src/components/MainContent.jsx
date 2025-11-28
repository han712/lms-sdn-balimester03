import { useState, useEffect } from "react";

// Import halaman
import Dashboard from "../pages/Dashboard";
import GuruStaffPage from "../pages/GuruStaffPage";
import DataSiswaPage from "../pages/DataSiswaPage";
import AbsensiSiswaPage from "../pages/AbsensiSiswaPage";
import EMateriPage from "../pages/EMateriPage";
import MataPelajaranPage from "../pages/MataPelajaranPage";

export default function MainContent() {
    const [page, setPage] = useState(window.location.hash || "#/dashboard");

    useEffect(() => {
        const onHashChange = () => setPage(window.location.hash);
        window.addEventListener("hashchange", onHashChange);
        return () => window.removeEventListener("hashchange", onHashChange);
    }, []);

    return (
        <div
            className="p-4"
            style={{
                backgroundColor: "#EFF0FF",
                minHeight: "calc(100vh - 60px)",
            }}
        >
            {page === "#/dashboard" && <Dashboard />}
            {page === "#/guru" && <GuruStaffPage />}
            {page === "#/siswa" && <DataSiswaPage />}
            {page === "#/absensi-siswa" && <AbsensiSiswaPage />}
            {page === "#/e-materi" && <EMateriPage />}
            {page === "#/mata-pelajaran" && <MataPelajaranPage />}
        </div>
    );
}
