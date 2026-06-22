import { Link, useNavigate, useLocation } from "react-router";
import { useEffect, useState, useRef } from "react";
import useAxiosPrivate from "../../Hooks/useAxiosPrivate";

const Activities = () => {
    const [activities, setActivities] = useState();
    const [errMsg, setErrMsg] = useState('');
    const errRef = useRef();
    const axiosPrivate = useAxiosPrivate();
    const navigate = useNavigate();
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
    
    return (
        <section>
            <h1>Activities Page</h1>
            <h2>Activities</h2>
                {activities?.data?.length
                    ? (
                        <table>
                            <tr>
                                <td>Title</td>
                                <td>Role</td>
                                <td>Location</td>
                                <td>Starts At</td>
                            </tr>
                            {
                                activities.data.map((activity, i) => 
                                    <tr key={i}>
                                        <td>{activity?.title}</td>
                                        <td>{activity?.role}</td>
                                        <td>{activity?.location}</td>
                                        <td>{activity?.starts_at}</td>
                                    </tr>
                                )
                            }
                        </table>
                    ) : <p>No Activities to display</p>
                }
                <p ref={errRef} className={errMsg ? "errmsg" : "offscreen"}  aria-live="assertive">{errMsg}</p>
                <br />
            <h2>Links</h2>
                <div className="flexGrow">
                    <Link to="/">Home</Link>
                </div>
        </section>
    )
}

export default Activities