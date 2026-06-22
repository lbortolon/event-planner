import {useRef, useState, useEffect} from 'react';
import { useNavigate, useLocation, Link, useParams } from "react-router";
import useAxiosPrivate from "../../Hooks/useAxiosPrivate";

export default function DeleteContactList() {
    const navigate = useNavigate();
    const location = useLocation();
    const axiosPrivate = useAxiosPrivate();
    const params = useParams();
    const effectRan = useRef(false);

    const [errMsg, setErrMsg] = useState('');
    const [succMsg, setSuccMsg] = useState('');

    useEffect(() => {
        setErrMsg('');
    }, [])

    useEffect(() => {
        const controller = new AbortController();
        if(effectRan.current === false) {
            const deleteContactList = async () => {
                try {
                    const response = await axiosPrivate.delete('/contact-lists/' + params.id,
                    {
                        signal: controller.signal
                    });
                    setSuccMsg('List deleted');
                    console.log(response?.data);
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

            deleteContactList();
                
            return () => {
                effectRan.current = true;
            }
        }
    }, []
    )

    return (
            <div className="DeleteContactList">
                    <section>
                        <h1>Delete List</h1>
                        <h2>Message</h2>
                        <p className={errMsg ? "errmsg" : "offscreen"}  aria-live="assertive">{errMsg}</p>
                        <p className={succMsg ? "succmsg" : "offscreen"}  aria-live="assertive">{succMsg}</p>
                        
                        <h2>Links</h2>
                        <div className="flexGrow">
                            <Link to="/contact-lists">Go to the lists page</Link>
                        </div>
                        <div className="flexGrow">
                            <Link to="/">Home</Link>
                        </div>
                    </section>            
            </div>
        );
}
