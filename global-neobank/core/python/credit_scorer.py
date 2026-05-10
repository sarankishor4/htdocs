def calculate_score(skills, history):
    # Base score
    score = 300
    
    # Each skill adds points based on demand
    high_value = ['Dev', 'Finance', 'Data', 'AI', 'Cloud']
    med_value = ['Design', 'Marketing', 'Support', 'Sales']
    
    for skill in skills:
        if skill in high_value:
            score += 150
        elif skill in med_value:
            score += 75
        else:
            score += 30
            
    # Cap at 1000
    return min(1000, score)
