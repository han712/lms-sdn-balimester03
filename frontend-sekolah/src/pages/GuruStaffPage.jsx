import GuruCard from "../components/GuruCard";

export default function GuruStaffPage() {
    const dataGuru = [
        {
            nama: "Ane Aini Fitriyah",
            tempatLahir: "Sukabumi",
            tanggalLahir: "02 April 1980",
            agama: "Islam",
            nip: "198004022023212016",
            mataPelajaran: "Pendidikan Agama Islam",
            foto: "/FotoGuru/Ane Aini Fitriyah.jpg",
        },
        {
            nama: "Erma Noviyanti",
            tempatLahir: "Jakarta",
            tanggalLahir: "29 November 1981",
            agama: "Islam",
            nip: "198111292022212012",
            mataPelajaran: "Matematika",
            foto: "/FotoGuru/Erma Novianti.jpeg",
        },
        {
            nama: "Haryanti",
            tempatLahir: "Jakarta",
            tanggalLahir: "18 Januari 1978",
            agama: "Islam",
            nip: "197801182022212005",
            mataPelajaran: "Penjaskes",
            foto: "/FotoGuru/Haryanti.jpeg",
        },
        {
            nama: "Dini Hildah",
            tempatLahir: "Jakarta",
            tanggalLahir: "29 Desember 1975",
            agama: "Islam",
            nip: "197512292014122003",
            mataPelajaran: "Penjaskes",
            foto: "/FotoGuru/Dini Hildah.jpeg",
        },
        {
            nama: "Oktaria Suraya",
            tempatLahir: "Yogyakarta",
            tanggalLahir: "14 Oktober 1983",
            agama: "Islam",
            nip: "198310142008012009",
            mataPelajaran: "Penjaskes",
            foto: "/FotoGuru/Oktaria Suraya.jpeg",
        },
        {
            nama: "Wahyu",
            tempatLahir: "Sumedang",
            tanggalLahir: "14 Mei 1967",
            agama: "Islam",
            nip: "196705142008011007",
            mataPelajaran: "Penjaskes",
            foto: "/FotoGuru/Wahyu.jpeg",
        },
        {
            nama: "Rudi Ferdinan",
            tempatLahir: "Jakarta",
            tanggalLahir: "26 September 1985",
            agama: "Islam",
            nip: "198509262022211006",
            mataPelajaran: "Penjaskes",
            foto: "/FotoGuru/Rudi Ferdinan.jpeg",
        },
    ];

    return (
        <>
            <h4 className="mb-3">Guru & Staff</h4>

            <div className="d-flex flex-wrap gap-4">
                {dataGuru.map((guru, index) => (
                    <GuruCard key={index} guru={guru} />
                ))}
            </div>
        </>
    );
}