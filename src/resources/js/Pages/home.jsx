import { useNavigate, Link } from "react-router";
import { useContext } from "react";
import AuthContext from "../Context/AuthProvider";

const Home = () => {
    const { setAuth } = useContext(AuthContext);
    const navigate = useNavigate();

    const logout = async () => {
        // if used in more components, this should be in context 
        // axios to /logout endpoint 
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