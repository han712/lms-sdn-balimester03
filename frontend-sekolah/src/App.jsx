import Sidebar from "./components/sidebar";
import Navbar from "./components/Navbar";
import MainContent from "./components/MainContent";
import "bootstrap/dist/css/bootstrap.min.css";
import "./App.css";

function App() {
    return (
        <div className="d-flex w-100" style={{ minHeight: "100vh" }}>
            {/* Sidebar permanen kiri */}
            <Sidebar />

            {/* Area kanan: Navbar + Content */}
            <div
                className="flex-grow-1 d-flex flex-column"
                style={{ backgroundColor: "#EFF0FF", minHeight: "100vh" }}
            >
                <Navbar />

                {/* Konten Scrollable */}
                <div className="flex-grow-1 overflow-auto">
                    <MainContent />
                </div>
            </div>
        </div>
    );
}

export default App;