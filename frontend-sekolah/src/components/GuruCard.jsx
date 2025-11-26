export default function GuruCard({ guru }) {
    return (
        <div
            className="card shadow-sm rounded-4 p-0"
            style={{ width: "260px" }}
        >
            <img
                src={guru.foto}
                alt={guru.nama}
                className="card-img-top"
                style={{
                    height: "200px",
                    objectFit: "cover",
                    borderTopLeftRadius: "12px",
                    borderTopRightRadius: "12px",
                }}
            />

            <div className="card-body" style={{ fontSize: "14px" }}>
                <p>
                    <strong>Nama :</strong> {guru.nama}
                </p>
                <p>
                    <strong>Tempat Lahir :</strong> {guru.tempatLahir}
                </p>
                <p>
                    <strong>Tanggal Lahir :</strong> {guru.tanggalLahir}
                </p>
                <p>
                    <strong>Agama :</strong> {guru.agama}
                </p>
                <p>
                    <strong>NIP :</strong> {guru.nip}
                </p>
                <p>
                    <strong>Guru :</strong> {guru.mataPelajaran}
                </p>
            </div>
        </div>
    );
}
