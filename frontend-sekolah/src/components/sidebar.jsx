import { useState } from "react";
import {
    BsHouse,
    BsBook,
    BsPeople,
    BsChevronDown,
    BsChevronUp,
    BsQuestionCircle
} from "react-icons/bs";

export default function Sidebar() {
    const [openSiswa, setOpenSiswa] = useState(false);
    const [openLms, setOpenLms] = useState(false);

    return (
        <div
            className="d-flex flex-column p-3 text-white"
            style={{
                backgroundColor: "#1C1F6A",
                width: "260px",
                minHeight: "100vh"
            }}
        >
            {/* Profile */}
            <div className="text-center mb-4">
                <div
                    className="rounded-circle mx-auto mb-2"
                    style={{
                        width: "55px",
                        height: "55px",
                        backgroundColor: "#E1E6F9"
                    }}
                ></div>
                <p className="mb-0 fw-bold">Hi, Asep</p>
                <small className="opacity-75">Selamat Belajar!</small>
            </div>

            <ul className="nav flex-column gap-1">
                <li className="nav-item">
                    <a href="#/dashboard" className="text-white text-decoration-none d-flex align-items-center gap-2 p-2 rounded hover-bg">
                        <BsHouse /> Dashboard
                    </a>
                </li>

                {/* Siswa */}
                <li className="nav-item">
                    <div
                        onClick={() => setOpenSiswa(!openSiswa)}
                        style={{ cursor: "pointer" }}
                        className="text-white d-flex align-items-center gap-2 p-2 rounded justify-content-between hover-bg"
                    >
                        <span><BsPeople /> Siswa</span>
                        {openSiswa ? <BsChevronUp /> : <BsChevronDown />}
                    </div>

                    {openSiswa && (
                        <ul className="ms-4 nav flex-column">
                            <li className="nav-item mt-2">
                                <a href="#/siswa" className="text-white text-decoration-none">Data Siswa</a>
                            </li>
                            <li className="nav-item mt-1">
                                <a href="#/absensi-siswa" className="text-white text-decoration-none">Absensi Siswa</a>
                            </li>
                        </ul>
                    )}
                </li>

                {/* LMS */}
                <li className="nav-item">
                    <div
                        onClick={() => setOpenLms(!openLms)}
                        style={{ cursor: "pointer" }}
                        className="text-white d-flex align-items-center gap-2 p-2 rounded justify-content-between hover-bg"
                    >
                        <span><BsBook /> LMS</span>
                        {openLms ? <BsChevronUp /> : <BsChevronDown />}
                    </div>

                    {openLms && (
                        <ul className="ms-4 nav flex-column">
                            <li className="nav-item mt-2">
                                <a href="#/e-materi" className="text-white text-decoration-none">E-Materi</a>
                            </li>
                            <li className="nav-item mt-1">
                                <a href="#/data-tugas" className="text-white text-decoration-none">Data Tugas</a>
                            </li>
                        </ul>
                    )}
                </li>

                <li className="nav-item">
                    <a href="#/mata-pelajaran" className="text-white text-decoration-none d-flex align-items-center gap-2 p-2 rounded hover-bg">
                        <BsBook /> Mata Pelajaran
                    </a>
                </li>

                <li className="nav-item">
                    <a href="#/guru" className="text-white text-decoration-none d-flex align-items-center gap-2 p-2 rounded hover-bg">
                        <BsPeople /> Guru & Staff
                    </a>
                </li>

                <li className="nav-item">
                    <a href="#/bantuan" className="text-white text-decoration-none d-flex align-items-center gap-2 p-2 rounded hover-bg">
                        <BsQuestionCircle /> Bantuan
                    </a>
                </li>
            </ul>
        </div>
    );
}