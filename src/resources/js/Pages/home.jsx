import { useNavigate, Link } from "react-router";
import useAuth from "../Hooks/useAuth";
import useAxiosPrivate from "../Hooks/useAxiosPrivate";

const Home = () => {
    const { setAuth } = useAuth();
    const navigate = useNavigate();
    const axiosPrivate = useAxiosPrivate();

    const logout = async () => {
        const controller = new AbortController();
        try {
            const response = await axiosPrivate.post('/logout', {}, {
                signal: controller.signal
            });
            console.log(response?.data);
        } catch(err) {
            console.log(err);
        }
        localStorage.removeItem('auth');
        setAuth({});
        navigate('/login');
    }

    return (
        <section>
            <h1>Home</h1>
            <br />
            <p>You are logged in!</p>
            <br />
            <Link to="/contact-lists">Go to the lists page</Link>
            <div className="flexGrow">
                <button onClick={logout}>Sign Out</button>
            </div>
        </section>
    )
}

export default Home