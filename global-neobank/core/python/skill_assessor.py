def assess_skills(text):
    # Dummy keyword extractor
    text = text.lower()
    found = []
    
    keywords = {
        'python': 'Dev', 'java': 'Dev', 'react': 'Dev', 'developer': 'Dev',
        'finance': 'Finance', 'accounting': 'Finance', 'trading': 'Finance',
        'design': 'Design', 'figma': 'Design', 'ui/ux': 'Design',
        'marketing': 'Marketing', 'seo': 'Marketing',
        'data': 'Data', 'sql': 'Data', 'machine learning': 'AI', 'ai': 'AI'
    }
    
    for kw, category in keywords.items():
        if kw in text and category not in found:
            found.append(category)
            
    return found
