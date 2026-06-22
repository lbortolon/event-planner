import { useNavigate, Link, useLocation } from "react-router";
import useAuth from "../Hooks/useAuth";
import useAxiosPrivate from "../Hooks/useAxiosPrivate";
import { useState, useRef, useEffect } from "react";

const Home = () => {
    const { auth, setAuth } = useAuth();
    const navigate = useNavigate();
    const axiosPrivate = useAxiosPrivate();
    const [activities, setActivities] = useState();
    const location = useLocation();
    const effectRan = useRef(false);

    useEffect(() => {
            const controller = new AbortController();

            if(effectRan.current === false) {
                const getActivities = async () => {
                    try {
                        const response = await axiosPrivate.get('/activities', {
                            signal: controller.signal
                        });
                        console.log(response?.data);
                        setActivities(response?.data);
                    } catch(err) {
                        if(!err?.response) {
                            setErrMsg('No Server response');
                            console.error(err);
                        } else if (err?.response?.status) {
                            setErrMsg(err?.response?.status + ' ' + err?.response?.data?.message);
                            console.error(err);
                            navigate('/login', { state: { from: location }, replace: true });
                        }
                    }            
                };

                getActivities();
                
                return () => {
                    effectRan.current = true;
                }
            }
            
        }, []
    )

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
            <p>{auth?.user}, You are logged in!</p>
            <h2>Activities</h2>
             <p>You have { activities?.data?.length } activities</p>
             <Link to="/activities">Go to the activities page</Link>
            <br />
            <h2>Lists</h2>
            <Link to="/contact-lists">Go to the lists page</Link>
            <br />
            <br />
            <br />
            <h2>Links / Logout</h2>
            <div className="flexGrow">
                <button onClick={logout}>Sign Out</button>
            </div>
        </section>
    )
}

export default Home