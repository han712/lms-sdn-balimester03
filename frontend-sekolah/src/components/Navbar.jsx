export default function Navbar() {
    return (
        <div style={{ width: "100%" }}>
            <nav
                className="px-4 py-3 text-center text-white"
                style={{ backgroundColor: "#0E1735" }}
            >
                <h6 className="mb-0 fw-bold">SDN Balimester 03 Petang</h6>
                <small className="opacity-75">
                    SDN Balimester 03 PetangJl. Matraman Raya No.177, Balimester,
                    Kec. Jatinegara, Jakarta Timur Prov. DKI Jakarta
                </small>
            </nav>

            <div
                className="text-center py-2 fw-semibold"
                style={{ backgroundColor: "#7AA0E8", color: "#fff", fontSize: "14px" }}
            >
                Beriman, Berprestasi, Berkarakter Pancasila
            </div>
        </div>
    );
}
