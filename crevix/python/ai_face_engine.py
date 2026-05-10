import sys
import os
import cv2
import mysql.connector
import json
import numpy as np

# Try to import advanced face recognition, fallback to OpenCV cascades
try:
    import face_recognition
    ADVANCED_MODE = True
except ImportError:
    ADVANCED_MODE = False
    print("AI: face_recognition not found. Falling back to OpenCV Cascades.")

def scan_media(media_id, file_path, db_config):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)

        print(f"AI: Scanning media {media_id} -> {file_path}")
        
        if not os.path.exists(file_path):
            print(f"AI Error: File not found {file_path}")
            return

        # Load known faces from DB
        cursor.execute("SELECT p.id, e.encoding FROM profiles p JOIN face_encodings e ON p.id = e.profile_id")
        known_faces = cursor.fetchall()
        
        known_encodings = []
        known_ids = []
        for k in known_faces:
            known_encodings.append(np.frombuffer(k['encoding'], dtype=np.float64))
            known_ids.append(k['id'])

        found_profiles = set()

        if ADVANCED_MODE:
            # === ADVANCED MODE (face_recognition) ===
            if file_path.lower().endswith(('.mp4', '.mkv', '.avi', '.mov')):
                cap = cv2.VideoCapture(file_path)
                count = 0
                while cap.isOpened():
                    ret, frame = cap.read()
                    if not ret: break
                    if count % 60 == 0:
                        rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
                        locs = face_recognition.face_locations(rgb)
                        encs = face_recognition.face_encodings(rgb, locs)
                        for enc in encs:
                            matches = face_recognition.compare_faces(known_encodings, enc, tolerance=0.6)
                            if True in matches:
                                found_profiles.add(known_ids[matches.index(True)])
                            else:
                                # New Profile
                                cursor.execute("INSERT INTO profiles (name) VALUES ('Unknown Person')")
                                pid = cursor.lastrowid
                                cursor.execute("INSERT INTO face_encodings (profile_id, encoding) VALUES (%s, %s)", (pid, enc.tobytes()))
                                known_encodings.append(enc)
                                known_ids.append(pid)
                                found_profiles.add(pid)
                                # Thumbnail
                                t, r, b, l = locs[0]
                                cv2.imwrite(f"uploads/media/profile_{pid}.jpg", frame[t:b, l:r])
                                cursor.execute("UPDATE profiles SET thumbnail=%s WHERE id=%s", (f"uploads/media/profile_{pid}.jpg", pid))
                    count += 1
                cap.release()
            else:
                img = face_recognition.load_image_file(file_path)
                encs = face_recognition.face_encodings(img)
                for enc in encs:
                    matches = face_recognition.compare_faces(known_encodings, enc)
                    if True in matches:
                        found_profiles.add(known_ids[matches.index(True)])
                    else:
                        cursor.execute("INSERT INTO profiles (name) VALUES ('Unknown Person')")
                        pid = cursor.lastrowid
                        cursor.execute("INSERT INTO face_encodings (profile_id, encoding) VALUES (%s, %s)", (pid, enc.tobytes()))
                        found_profiles.add(pid)
                        # Thumbnail (simplified)
                        cv2.imwrite(f"uploads/media/profile_{pid}.jpg", cv2.cvtColor(img, cv2.COLOR_RGB2BGR))
                        cursor.execute("UPDATE profiles SET thumbnail=%s WHERE id=%s", (f"uploads/media/profile_{pid}.jpg", pid))

        else:
            # === FALLBACK MODE (OpenCV Cascades) ===
            face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
            if file_path.lower().endswith(('.mp4', '.mkv', '.avi', '.mov')):
                cap = cv2.VideoCapture(file_path)
                while cap.isOpened():
                    ret, frame = cap.read()
                    if not ret: break
                    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
                    faces = face_cascade.detectMultiScale(gray, 1.1, 4)
                    for (x, y, w, h) in faces:
                        face_roi = gray[y:y+h, x:x+w]
                        face_roi_resized = cv2.resize(face_roi, (100, 100))
                        
                        # Simple feature matching: Use histogram of the face
                        hist = cv2.calcHist([face_roi_resized], [0], None, [256], [0, 256])
                        cv2.normalize(hist, hist)
                        hist_flat = hist.flatten()
                        
                        # Compare with existing profile histograms
                        cursor.execute("SELECT profile_id, encoding FROM face_encodings")
                        all_enc = cursor.fetchall()
                        best_match_id = None
                        best_score = 0.5 # Threshold
                        
                        for e in all_enc:
                            saved_hist = np.frombuffer(e['encoding'], dtype=np.float32)
                            score = cv2.compareHist(hist_flat, saved_hist, cv2.HISTCMP_CORREL)
                            if score > 0.85 and score > best_score: # 0.85 correlation is a good match
                                best_score = score
                                best_match_id = e['profile_id']
                        
                        if best_match_id:
                            pid = best_match_id
                        else:
                            # New unique person
                            cursor.execute("INSERT INTO profiles (name) VALUES ('Identified Person')")
                            pid = cursor.lastrowid
                            cursor.execute("INSERT INTO face_encodings (profile_id, encoding) VALUES (%s, %s)", (pid, hist_flat.tobytes()))
                            # Save Thumbnail
                            thumb_path = f"uploads/media/profile_{pid}.jpg"
                            cv2.imwrite(thumb_path, frame[y:y+h, x:x+w])
                            cursor.execute("UPDATE profiles SET thumbnail=%s WHERE id=%s", (thumb_path, pid))
                        
                        found_profiles.add(pid)
                    if len(faces) > 0: break # Process one frame with faces and move on
                cap.release()
            else:
                img = cv2.imread(file_path)
                gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
                faces = face_cascade.detectMultiScale(gray, 1.1, 4)
                if len(faces) > 0:
                    cursor.execute("SELECT id FROM profiles WHERE name='Detected Person' LIMIT 1")
                    p = cursor.fetchone()
                    if p: pid = p['id']
                    else:
                        cursor.execute("INSERT INTO profiles (name) VALUES ('Detected Person')")
                        pid = cursor.lastrowid
                    found_profiles.add(pid)

        # Link profiles to media
        for pid in found_profiles:
            cursor.execute("INSERT IGNORE INTO media_faces (media_id, profile_id) VALUES (%s, %s)", (media_id, pid))

        conn.commit()
        conn.close()
        print("AI: Scan Success!")

    except Exception as e:
        print(f"AI Error: {str(e)}")

if __name__ == "__main__":
    if len(sys.argv) > 2:
        scan_media(sys.argv[1], sys.argv[2], {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': 'crevix_db'
        })
