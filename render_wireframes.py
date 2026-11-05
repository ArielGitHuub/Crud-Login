import os
import time
from playwright.sync_api import sync_playwright

def render():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    wireframes_dir = os.path.join(base_dir, "wireframes")
    
    files = [
        ("figura1_dashboard.html", "figura1_dashboard.png"),
        ("figura2_catalogo_menu.html", "figura2_catalogo_menu.png"),
        ("figura3_detalle_plato.html", "figura3_detalle_plato.png")
    ]
    
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="msedge", headless=True)
        # Using device_scale_factor=2 for crisp, ultra-sharp retina screenshots
        context = browser.new_context(
            viewport={"width": 1200, "height": 850},
            device_scale_factor=2
        )
        page = context.new_page()
        
        for html_name, png_name in files:
            html_path = os.path.join(wireframes_dir, html_name).replace("\\", "/")
            png_path = os.path.join(wireframes_dir, png_name)
            
            file_url = f"file:///{html_path}"
            print(f"Loading {file_url}...")
            page.goto(file_url, wait_until="networkidle")
            # Wait a brief moment for Tailwind CDN font and render
            page.wait_for_timeout(800)
            
            # Select the wireframe container or take full page screenshot
            container = page.locator("body > div")
            if container.count() > 0:
                container.screenshot(path=png_path)
            else:
                page.screenshot(path=png_path)
            print(f"Saved: {png_path}")
            
        browser.close()
        print("All wireframes rendered successfully!")

if __name__ == "__main__":
    render()
